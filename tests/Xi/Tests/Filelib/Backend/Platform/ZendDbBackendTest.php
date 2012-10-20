<?php

namespace Xi\Tests\Filelib\Backend\Platform;

use Xi\Filelib\Backend\Platform\ZendDbBackend;
use Zend_Db;

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
        if (!class_exists('Zend_Db')) {
            $this->markTestSkipped("Zend Db could not be loaded");
        }

        parent::setUp();
    }

    public function tearDown()
    {
        if (!class_exists('Zend_Db')) {
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

        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        return new ZendDbBackend($ed, $db);
    }

    /**
     * @test
     */
    public function zendDbGettersShouldReturnCorrectObjects()
    {
        $this->setUpEmptyDataSet();

        $this->assertInstanceOf(
            'Xi\Filelib\Backend\Platform\ZendDb\FileTable',
            $this->backend->getFileTable()
        );

        $this->assertInstanceOf(
            'Xi\Filelib\Backend\Platform\ZendDb\FolderTable',
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
     */
    public function getsAndSetsResourceTable()
    {
        $this->setUpEmptyDataSet();

        $resourceTable = $this->getMockBuilder('Xi\Filelib\Backend\Platform\ZendDb\ResourceTable')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->assertInstanceOf('Zend_Db_Table_Abstract', $this->backend->getFolderTable());
        $this->assertNotSame($resourceTable, $this->backend->getResourceTable());

        $this->backend->setResourceTable($resourceTable);
        $this->assertSame($resourceTable, $this->backend->getResourceTable());
    }


    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function createFolderTableMock()
    {
        return $this->getMockAndDisableOriginalConstructor(
            'Xi\Filelib\Backend\Platform\ZendDb\FolderTable'
        );
    }
}
