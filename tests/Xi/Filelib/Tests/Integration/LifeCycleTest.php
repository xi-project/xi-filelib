<?php

namespace Xi\Filelib\Tests\Integration;


use Xi\Filelib\Authorization\AutomaticPublisherPlugin;

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

}
