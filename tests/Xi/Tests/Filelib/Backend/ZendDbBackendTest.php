<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ZendDbBackend;
use Xi\Filelib\Folder\FolderItem;
use Zend_Db;
use Exception;

/**
 * @author pekkis
 *
 * @group  backend
 * @group  zenddb
 */
class ZendDbBackendTest extends RelationalDbTestCase
{
    public function setUp()
    {
        if (!class_exists('\Zend_Db')) {
            $this->markTestSkipped("Zend Db could not be loaded");
        }
        
        parent::setUp();
    }

    public function tearDown()
    {
        if (!class_exists('\Zend_Db')) {
            $this->markTestSkipped("Zend Db could not be loaded");
        }
        
        parent::tearDown();
    }

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

        return new ZendDbBackend($db);
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
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createFolderTableMock()
    {
        return $this->getMockAndDisableOriginalConstructor(
            'Xi\Filelib\Backend\ZendDb\FolderTable'
        );
    }
}
