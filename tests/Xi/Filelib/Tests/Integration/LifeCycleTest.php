<?php

namespace Xi\Filelib\Tests\Integration;


use Xi\Filelib\Authorization\AutomaticPublisherPlugin;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\File\File;

use Pekkis\Queue\Adapter\PhpAMQPAdapter;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Profile\FileProfile;

class LifeCycleTest extends TestCase
{
    /**
     * @test
     */
    public function nothingIsFoundAfterDeleting()
    {
        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $folder = $this->filelib->getFolderRepository()->createByUrl('imaiseppa/mehevaa/soprano/ja/arto-tenhunen');

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);

        $file = $this->filelib->upload($manateePath, $folder);
        $this->assertEquals(File::STATUS_COMPLETED, $file->getStatus());
        $this->assertPublisherFileCount(0);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

        $allFiles = $this->filelib->getFileRepository()->findAll();
        $this->assertCount(1, $allFiles);
        $this->assertSame($file, $allFiles->current());

        $this->filelib->getFileRepository()->delete($file);

        $allFiles = $this->filelib->getFileRepository()->findAll();
        $this->assertCount(0, $allFiles);

        $this->assertStorageFileCount(3);
        $this->assertPublisherFileCount(0);

        $allResources = $this->filelib->getResourceRepository()->findAll();
        $this->assertCount(1, $allResources);

        $secondFile =  $this->filelib->upload($manateePath);
        $this->assertSame($file->getResource(), $secondFile->getResource());

        $this->publisher->publish($secondFile);
        $this->assertStorageFileCount(3);
        $this->assertPublisherFileCount(2);

        $this->filelib->getFileRepository()->delete($secondFile);
        $this->assertStorageFileCount(3);
        $this->assertPublisherFileCount(0);

        $this->filelib->getResourceRepository()->delete($allResources->current());
        $this->assertStorageFileCount(0);

        $allResources = $this->filelib->getResourceRepository()->findAll();
        $this->assertCount(0, $allResources);
    }

    /**
     * @test
     */
    public function automaticPublisherPublishesAutomatically()
    {
        $automaticPublisherPlugin = new AutomaticPublisherPlugin(
            $this->publisher,
            $this->authorizationAdapter
        );
        $this->filelib->addPlugin($automaticPublisherPlugin);

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file =  $this->filelib->upload($manateePath);

        $this->assertPublisherFileCount(2);
    }

    /**
     * @test
     */
    public function canUploadAsynchronouslyWithQueue()
    {
        if (!RABBITMQ_HOST) {
            return $this->markTestSkipped('RabbitMQ not configured');
        }

        $adapter = new PhpAMQPAdapter(
            RABBITMQ_HOST,
            RABBITMQ_PORT,
            RABBITMQ_USERNAME,
            RABBITMQ_PASSWORD,
            RABBITMQ_VHOST,
            'filelib_test_exchange',
            'filelib_test_queue'
        );

        $queue = $this->filelib->createQueueFromAdapter(
            $adapter
        )->getQueue();
        $queue->purge();

        $this->filelib->getFileRepository()->setExecutionStrategy(
            FileRepository::COMMAND_AFTERUPLOAD,
            ExecutionStrategy::STRATEGY_ASYNCHRONOUS
        );

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->upload($manateePath);
        $this->assertEquals(File::STATUS_RAW, $file->getStatus());

        $msg = $queue->dequeue();

        $command = $msg->getData();
        $command->execute();

        $this->assertEquals(File::STATUS_COMPLETED, $file->getStatus());
    }

    /**
     * @test
     */
    public function uploadsToUnspoiledProfile()
    {
        $this->filelib->addProfile(new FileProfile('unspoiled'));

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file1 = $this->filelib->upload(new FileUpload($manateePath));
        $file2 = $this->filelib->upload($manateePath, null, 'unspoiled');

        // @todo Why profile manager of all places?
        $this->assertTrue($this->filelib->getProfileManager()->hasVersion($file1, 'cinemascope'));
        $this->assertFalse($this->filelib->getProfileManager()->hasVersion($file2, 'cinemascope'));

        $this->assertFalse($file1->hasVersion('cinemascope'));
        $this->assertFalse($file2->hasVersion('cinemascope'));

        $this->assertSame($file1->getResource(), $file2->getResource());
        $this->assertTrue($file1->getResource()->hasVersion('cinemascope'));
    }

}
