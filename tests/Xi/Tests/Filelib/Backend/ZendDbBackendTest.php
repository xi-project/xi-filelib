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
