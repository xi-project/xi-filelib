<?php

namespace Xi\Tests\Filelib\Backend;

use PDO;
use PDOException;
use PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection;
use PHPUnit_Extensions_Database_DB_MetaData_MySQL;
use PHPUnit_Extensions_Database_DefaultTester;
use PHPUnit_Extensions_Database_Operation_Factory;
use PHPUnit_Extensions_Database_Operation_Composite;
use PHPUnit_Extensions_Database_Operation_IDatabaseOperation;
use PHPUnit_Extensions_Database_DataSet_AbstractDataSet;
use PHPUnit_Extensions_Database_DataSet_DefaultDataSet;
use Exception;
use DateTime;
use Xi\Tests\PHPUnit\Extensions\Database\Operation\MySQL55Truncate;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 * @author Mikko Forsström <pekkisx@gmail.com>
 *
 * @group backend
 */
abstract class RelationalDbTestCase extends AbstractBackendTest
{
    /**
     * @var string|null
     */
    private $dataSet;

    /**
     * @var
     */
    private $databaseTester;

    /**
     * @throws Exception If no data set was used.
     */
    protected function tearDown()
    {
        $this->databaseTester->setTearDownOperation($this->getTearDownOperation());
        $this->databaseTester->setDataSet($this->getDataSet($this->dataSet));
        $this->databaseTester->onTearDown();
        $this->databaseTester = null;

        $this->dataSet = null;

        gc_collect_cycles();

    }

    /**
     * Implements AbstractBackendTest::setUpEmptyDataSet
     *
     * Set up a test using an empty data set.
     */
    protected function setUpEmptyDataSet()
    {
        $this->setUpDataSet('empty');
    }

    /**
     * Implements AbstractBackendTest::setUpSimpleDataSet
     *
     * Set up a test using a simple data set.
     */
    protected function setUpSimpleDataSet()
    {
        $this->setUpDataSet('simple');
    }

    /**
     * @return array
     */
    public function referenceCountProvider()
    {
        return array(
            array(1, 1),
            array(1, 2),
            array(1, 3),
            array(2, 4),
        );
    }

    /**
     * @return array
     */
    public function findResourceProvider()
    {
        return array(
            array(1, array('hash' => 'hash-1', 'versions' => array('tussi', 'watussi', 'pygmi'))),
            array(2, array('hash' => 'hash-2', 'versions' => array())),
            array(3, array('hash' => 'hash-2', 'versions' => array('pygmi', 'tussi'))),
            array(4, array('hash' => 'hash-3', 'versions' => array('watussi'))),
        );
    }

    /**
     * @return array
     */
    public function nonExistingResourceIdProvider()
    {
        return array(
            array(6),
            array(66),
            array(666),
            array(6666)
        );
    }


    public function identifierValidityProvider()
    {
        return array(
            array(true, 1),
            array(false, 'xooxer'),
            array(true, 45393),
            array(false, ''),
        );
    }


    /**
     * @return array
     */
    public function resourceHashProvider()
    {
        return array(
            array('hash-1', 1),
            array('hash-2', 2),
            array('hash-3', 1),
            array('hash-4', 0),
            array('hash-666', 0),
        );
    }

    /**
     * @return array
     */
    public function orphanResourceIdProvider()
    {
        return array(
            array(5),
        );
    }

    /**
     * @return array
     */
    public function resourceIdWithReferencesProvider()
    {
        return array(
            array(3),
        );
    }


    /**
     * @return array
     */
    public function provideForFindFolder()
    {
        return array(
            array(1, array('name' => 'root')),
            array(2, array('name' => 'lussuttaja')),
            array(3, array('name' => 'tussin')),
            array(4, array('name' => 'banskun')),
        );
    }

    /**
     * @return array
     */
    public function filelessFolderIdProvider()
    {
        return array(
            array(5),
        );
    }

    /**
     * @return array
     */
    public function updateFolderProvider()
    {
        return array(
            array(3, 2, 1)
        );
    }

    /**
     * @return array
     */
    public function subFolderProvider()
    {
        return array(
            array(1, 1),
            array(2, 3),
            array(4, 0),
        );
    }

    /**
     * @return array
     */
    public function folderByUrlProvider()
    {
        return array(
            array('lussuttaja/tussin', 3),
        );
    }

    /**
     * @return array
     */
    public function findFilesInProvider()
    {
        return array(
            array(1, 1),
            array(4, 2),
            array(5, 0),
        );
    }

    /**
     * @return array
     */
    public function folderIdWithFilesProvider()
    {
        return array(
            array(1),
        );
    }

    /**
     * @return array
     */
    public function findFileProvider()
    {
        return array(
            array(1),
        );
    }

    /**
     * @return array
     */
    public function updateFileProvider()
    {
        return array(
            array(1, 2, 2),
        );
    }

    /**
     * @return array
     */
    public function deleteFileProvider()
    {
        return array(
            array(5),
        );
    }

    /**
     * @return array
     */
    public function folderIdProvider()
    {
        return array(
            array(1),
        );
    }

    /**
     * @return array
     */
    public function findFileByFilenameProvider()
    {
        return array(
            array(1, 1, 1),
        );
    }

    /**
     * @return array
     */
    public function invalidFolderIdProvider()
    {
        return array(
            array('xoo', 'an integer'),
        );
    }

    /**
     * @return array
     */
    public function invalidFileIdProvider()
    {
        return array(
            array('xoo', 'an integer'),
        );
    }

    /**
     * @return array
     */
    public function notFoundFolderIdProvider()
    {
        return array(
            array(666),
        );
    }

    /**
     * @return array
     */
    public function parentFolderIdProvider()
    {
        return array(
            array(3),
        );
    }

    /**
     * @return array
     */
    public function rootFolderIdProvider()
    {
        return array(
            array(1),
        );
    }

    /**
     * @return array
     */
    public function findFolderProvider()
    {
        return array(
            array(1, array('name' => 'root')),
            array(2, array('name' => 'lussuttaja')),
            array(3, array('name' => 'tussin')),
            array(4, array('name' => 'banskun')),
        );
    }

    /**
     * @return array
     */
    public function nonExistingFolderIdProvider()
    {
        return array(
            array(900),
        );
    }

    /**
     *
     * @return array
     */
    public function updateResourceProvider()
    {
        return array(
            array(
                1, array('imaisebba', 'tussia'),
            )
        );
    }


    /**
     * @return ArrayDataSet
     */
    private function getSimpleDataSet()
    {
        return new ArrayDataSet(array(

            'xi_filelib_resource' => array(
                array(
                    'id' => 1,
                    'hash' => 'hash-1',
                    'date_created' => '1978-03-21 06:06:06',
                    'versions' => serialize(array('tussi', 'watussi', 'pygmi')),
                ),
                array(
                    'id' => 2,
                    'hash' => 'hash-2',
                    'date_created' => '1988-03-21 06:06:06',
                    'versions' => serialize(array()),
                ),
                array(
                    'id' => 3,
                    'hash' => 'hash-2',
                    'date_created' => '1998-03-21 06:06:06',
                    'versions' => serialize(array('pygmi', 'tussi')),
                ),
                array(
                    'id' => 4,
                    'hash' => 'hash-3',
                    'date_created' => '2008-03-21 06:06:06',
                    'versions' => serialize(array('watussi')),
                ),
                array(
                    'id' => 5,
                    'hash' => 'hash-5',
                    'date_created' => '2009-03-21 06:06:06',
                    'versions' => serialize(array('watussi', 'loso')),
                ),
            ),

            'xi_filelib_folder' => array(
                array(
                    'id'         => 1,
                    'parent_id'  => null,
                    'folderurl'  => '',
                    'foldername' => 'root',
                    'uuid' => 'uuid-f-1',
                ),
                array(
                    'id'         => 2,
                    'parent_id'  => 1,
                    'folderurl'  => 'lussuttaja',
                    'foldername' => 'lussuttaja',
                    'uuid' => 'uuid-f-2',
                ),
                array(
                    'id'         => 3,
                    'parent_id'  => 2,
                    'folderurl'  => 'lussuttaja/tussin',
                    'foldername' => 'tussin',
                    'uuid' => 'uuid-f-3',
                ),
                array(
                    'id'         => 4,
                    'parent_id'  => 2,
                    'folderurl'  => 'lussuttaja/banskun',
                    'foldername' => 'banskun',
                    'uuid' => 'uuid-f-4',
                ),
                array(
                    'id'         => 5,
                    'parent_id'  => 2,
                    'folderurl'  => 'lussuttaja/tiedoton-kansio',
                    'foldername' => 'tiedoton-kansio',
                    'uuid' => 'uuid-f-5',
                ),
            ),
            'xi_filelib_file' => array(
                array(
                    'id'            => 1,
                    'folder_id'     => 1,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'versioned',
                    'filesize'      => '1000',
                    'filename'      => 'tohtori-vesala.png',
                    'filelink'      => 'tohtori-vesala.png',
                    'date_uploaded' => '2011-01-01 16:16:16',
                    'status'        => 1,
                    'uuid'          => 'uuid-1',
                    'resource_id'   => 1,
                ),
                array(
                    'id'            => 2,
                    'folder_id'     => 2,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'versioned',
                    'filesize'      => '10001',
                    'filename'      => 'akuankka.png',
                    'filelink'      => 'lussuttaja/akuankka.png',
                    'date_uploaded' => '2011-01-01 15:15:15',
                    'status'        => 2,
                    'uuid'          => 'uuid-2',
                    'resource_id'   => 2,
                ),
                array(
                    'id'            => 3,
                    'folder_id'     => 3,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'default',
                    'filesize'      => '10000',
                    'filename'      => 'repesorsa.png',
                    'filelink'      => 'lussuttaja/tussin/repesorsa.png',
                    'date_uploaded' => '2011-01-01 15:15:15',
                    'status'        => 3,
                    'uuid'          => 'uuid-3',
                    'resource_id'   => 3,
                ),
                array(
                    'id'            => 4,
                    'folder_id'     => 4,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'default',
                    'filesize'      => '10000',
                    'filename'      => 'megatussi.png',
                    'filelink'      => 'lussuttaja/banskun/megatussi.png',
                    'date_uploaded' => '2011-01-02 15:15:15',
                    'status'        => 4,
                    'uuid'          => 'uuid-4',
                    'resource_id'   => 4,
                ),
                array(
                    'id'            => 5,
                    'folder_id'     => 4,
                    'mimetype'      => 'image/png',
                    'fileprofile'   => 'default',
                    'filesize'      => '10000',
                    'filename'      => 'megatussi2.png',
                    'filelink'      => 'lussuttaja/banskun/megatussi2.png',
                    'date_uploaded' => '2011-01-03 15:15:15',
                    'status'        => 5,
                    'uuid'          => 'uuid-5',
                    'resource_id'   => 4,
                ),
            ),
        ));
    }

    /**
     * @param string $dataSet
     */
    private function setUpDataSet($dataSet)
    {
        $this->dataSet = $dataSet;

        $this->databaseTester = new PHPUnit_Extensions_Database_DefaultTester($this->getConnection());
        $this->databaseTester->setSetUpOperation($this->getSetUpOperation());
        $this->databaseTester->setDataSet($this->getDataSet($dataSet));
        $this->databaseTester->onSetUp();
    }

    /**
     * @param  string                                              $dataSet
     * @return PHPUnit_Extensions_Database_DataSet_AbstractDataSet
     * @throws Exception
     */
    private function getDataSet($dataSet = null)
    {
        if ($dataSet === 'empty') {
            return $this->getEmptyDataSet();
        } else if ($dataSet === 'simple') {
            return $this->getSimpleDataSet();
        }

        throw new Exception('Please specify a data set to be used.');
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_DefaultDataSet
     */
    private function getEmptyDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }

    /**
     * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    private function getConnection()
    {
        try {
            if (PDO_DRIVER === 'sqlite') {
                $pdo = new PDO(sprintf('sqlite:%s', PDO_DBNAME));
            } else {
                $dsn = sprintf('%s:host=%s;dbname=%s', PDO_DRIVER, PDO_HOST, PDO_DBNAME);

                $pdo = new PDO($dsn, PDO_USERNAME, PDO_PASSWORD);
            }
        } catch (PDOException $e) {
            $this->markTestSkipped('Could not connect to database.');
        }

        return new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($pdo);
    }

    /**
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    protected function getSetUpOperation()
    {
        if ($this->isMySQL()) {
            return new PHPUnit_Extensions_Database_Operation_Composite(array(
                new MySQL55Truncate(true),
                PHPUnit_Extensions_Database_Operation_Factory::INSERT()
            ));
        }

        return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
    }

    /**
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    protected function getTearDownOperation()
    {
        if ($this->isMySQL()) {
            return new PHPUnit_Extensions_Database_Operation_Composite(array(
                new MySQL55Truncate(true)
            ));
        }

        return PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
    }

    /**
     * @return boolean
     */
    private function isMySQL()
    {
        return $this->getConnection()->getMetaData() instanceof PHPUnit_Extensions_Database_DB_MetaData_MySQL;
    }
}
