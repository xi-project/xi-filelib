<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ZendDbBackend,
    Xi\Filelib\Folder\FolderItem,
    Zend_Db,
    Exception;

/**
 * @author pekkis
 * @group  zenddb
 */
class ZendDbBackendTest extends RelationalDbTestCase
{
    /**
     * @return ZendDbBackend
     */
    protected function setUpBackend()
    {
        $db = Zend_Db::factory('pdo_' . PDO_DRIVER, array(
            'host'     => PDO_HOST,
            'dbname'   => PDO_DBNAME,
            'username' => PDO_USERNAME,
            'password' => PDO_PASSWORD,
        ));

        $backend = new ZendDbBackend();
        $backend->setDb($db);

        return $backend;
    }

    /**
     * @test
     */
    public function zendDbGettersShouldReturnCorrectObjects()
    {
        $this->setUpEmptyDataSet();

        $this->assertInstanceOf(
            'Xi\Filelib\Backend\ZendDb\FileTable',
            $this->backend->getFileTable()
        );

        $this->assertInstanceOf(
            'Xi\Filelib\Backend\ZendDb\FolderTable',
            $this->backend->getFolderTable()
        );
    }

    /**
     * @test
     */
    public function getsAndSetsFolderTable()
    {
        $this->setUpEmptyDataSet();

        $folderTable = $this->createFolderTableMock();

        $this->assertNotSame($folderTable, $this->backend->getFolderTable());

        $this->backend->setFolderTable($folderTable);

        $this->assertSame($folderTable, $this->backend->getFolderTable());
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function updateFolderRethrowsException()
    {
        $this->setUpEmptyDataSet();

        $folderTable = $this->createFolderTableMock();
        $folderTable->expects($this->once())
                    ->method('getAdapter')
                    ->will($this->throwException(new Exception()));

        $folder = FolderItem::create(array(
            'id'        => 1,
            'parent_id' => null,
            'name'      => '',
        ));

        $this->backend->setFolderTable($folderTable);
        $this->backend->updateFolder($folder);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createFolderTableMock()
    {
        return $this->getMockAndDisableOriginalConstructor(
            'Xi\Filelib\Backend\ZendDb\FolderTable'
        );
    }
}
