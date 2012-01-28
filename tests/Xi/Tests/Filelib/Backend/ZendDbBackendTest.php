<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\ZendDbBackend,
    Zend_Db;

/**
 * @author pekkis
 * @group  zenddb
 */
class ZendDbBackendTest extends RelationalDbTestCase
{
    /**
     * @var ZendDbBackend
     */
    protected $backend;

    public function setUp()
    {
        parent::setUp();

        $db = Zend_Db::factory('pdo_' . PDO_DRIVER, array(
            'host'     => PDO_HOST,
            'dbname'   => PDO_DBNAME,
            'username' => PDO_USERNAME,
            'password' => PDO_PASSWORD,
        ));

        $this->backend = new ZendDbBackend();
        $this->backend->setDb($db);
    }

    /**
     * @test
     */
    public function zendDbGettersShouldReturnCorrectObjects()
    {
        $this->assertInstanceOf(
            'Xi\Filelib\Backend\ZendDb\FileTable',
            $this->backend->getFileTable()
        );

        $this->assertInstanceOf(
            'Xi\Filelib\Backend\ZendDb\FolderTable',
            $this->backend->getFolderTable()
        );
    }
}
