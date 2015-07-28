<?php

namespace Xi\Filelib\Tests\Integration;


use Xi\Filelib\Authorization\AutomaticPublisherPlugin;
use Xi\Filelib\File\File;

use Pekkis\Queue\Adapter\PhpAMQPAdapter;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Profile\FileProfile;

class LifeCycleTest extends TestCase
{
    /**
     * @return array
     */
    public function provideParams()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @test
     * @dataProvider provideParams
     * @coversNothing
     */
    public function nothingIsFoundAfterDeleting($isCached)
    {
        $this->setupCache($isCached);

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $folder = $this->filelib->getFolderRepository()->createByUrl('imaiseppa/mehevaa/soprano/ja/arto-tenhunen');

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);

        $file = $this->filelib->uploadFile($manateePath, $folder);

        $this->assertEquals(File::STATUS_COMPLETED, $file->getStatus());
        $this->assertPublisherFileCount(0);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

        $allFiles = $this->filelib->getFileRepository()->findAll();
        $this->assertCount(1, $allFiles);
        $this->assertSame($file, $allFiles->first());

        $this->filelib->getFileRepository()->delete($file);

        $allFiles = $this->filelib->getFileRepository()->findAll();
        $this->assertCount(0, $allFiles);

        $this->assertStorageFileCount(4);
        $this->assertPublisherFileCount(0);

        $allResources = $this->filelib->getResourceRepository()->findAll();
        $this->assertCount(1, $allResources);

        $secondFile =  $this->filelib->uploadFile($manateePath);
        $this->assertSame($file->getResource(), $secondFile->getResource());

        $this->publisher->publishAllVersions($secondFile);
        $this->assertStorageFileCount(4);
        $this->assertPublisherFileCount(3);

        $this->filelib->getFileRepository()->delete($secondFile);
        $this->assertStorageFileCount(4);
        $this->assertPublisherFileCount(0);

        $this->filelib->getResourceRepository()->delete($allResources->first());
        $this->assertStorageFileCount(0);

        $allResources = $this->filelib->getResourceRepository()->findAll();
        $this->assertCount(0, $allResources);
    }

    /**
     * @test
     * @dataProvider provideParams
     * @coversNothing
     */
    public function automaticPublisherPublishesAutomatically($isCached)
    {
        $this->setupCache($isCached);

        $automaticPublisherPlugin = new AutomaticPublisherPlugin(
            $this->publisher,
            $this->authorizationAdapter
        );
        $this->filelib->addPlugin($automaticPublisherPlugin);

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file =  $this->filelib->uploadFile($manateePath);

        $this->assertPublisherFileCount(3);
    }

    /**
     * @test
     * @dataProvider provideParams
     * @coversNothing
     */
    public function uploadsToUnspoiledProfile($isCached)
    {
        $this->setupCache($isCached);

        $this->filelib->addProfile(new FileProfile('unspoiled'));

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file1 = $this->filelib->uploadFile(new FileUpload($manateePath));
        $file2 = $this->filelib->uploadFile($manateePath, null, 'unspoiled');

        // @todo Why profile manager of all places?
        $this->assertTrue($this->filelib->getProfileManager()->hasVersion($file1, Version::get('cinemascope')));
        $this->assertFalse($this->filelib->getProfileManager()->hasVersion($file2, Version::get('cinemascope')));

        $this->assertFalse($file1->hasVersion(Version::get('cinemascope')));
        $this->assertFalse($file2->hasVersion(Version::get('cinemascope')));

        $this->assertSame($file1->getResource(), $file2->getResource());
        $this->assertTrue($file1->getResource()->hasVersion(Version::get('cinemascope')));
    }

    /**
     * @test
     * @dataProvider provideParams
     * @coversNothing
     */
    public function versionsMatch($isCached)
    {
        $this->setupCache($isCached);

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->uploadFile(new FileUpload($manateePath));

        $resource = $file->getResource();

        $this->assertEquals(
            array('original', 'cinemascope', 'croppo'),
            $resource->getVersions()
        );

        $this->filelib->getBackend()->getIdentityMap()->clear();

        $file2 = $this->filelib->findFile($file->getId());

        $this->assertEquals($file, $file2);

        // $this->assertTrue($resource->hasVersion(Version::get('cinemascope')));

        // die('xoo');
    }


    /**
     * @test
     * @dataProvider provideParams
     * @coversNothing
     */
    public function updatingFileUpdatesResource($isCached)
    {
        $this->setupCache($isCached);

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->uploadFile(new FileUpload($manateePath));

        $resource = $file->getResource();
        $this->assertEquals(array('original', 'cinemascope', 'croppo'), $resource->getVersions());

        $this->filelib->getBackend()->getIdentityMap()->clear();

        $file2 = $this->filelib->getFileRepository()->find($file->getId());
        $resource2 = $file2->getResource();

        $this->assertNotSame($file, $file2);
        $this->assertNotSame($resource, $resource2);

        $this->assertEquals(array('original', 'cinemascope', 'croppo'), $resource2->getVersions());
        $resource2->addVersion(Version::get('lussogrande'));

        $this->filelib->getFileRepository()->update($file2);
        $this->filelib->getBackend()->getIdentityMap()->clear();

        $row = $this->conn->fetchAssoc("SELECT * FROM xi_filelib_resource WHERE id = ?", [$file->getId()]);

        $file3 = $this->filelib->getFileRepository()->find($file->getId());
        $resource3 = $file3->getResource();

        $this->assertNotSame($file2, $file3);
        $this->assertNotSame($resource2, $resource3);
        $this->assertEquals(array('original', 'cinemascope', 'croppo', 'lussogrande'), $resource3->getVersions());
    }
}
