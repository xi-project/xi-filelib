<?php

namespace Xi\Filelib\Tests\Backend\Platform;

use Xi\Filelib\Backend\Platform\DoctrineDbalPlatform;
use Doctrine\DBAL\DriverManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class DoctrineDbalPlatformTest extends RelationalDbTestCase
{

    /**
     * @return DoctrineDbalPlatform
     */
    protected function setUpBackend()
    {
        $conn = DriverManager::getConnection(
            array(
                'driver' => 'pdo_' . PDO_DRIVER,
                'dbname' => PDO_DBNAME,
                'user' => PDO_USERNAME,
                'password' => PDO_PASSWORD,
                'host' => PDO_HOST,
            )
        );
        return new DoctrineDbalPlatform($conn);
    }

    /**
     * @test
     */
    public function failsWhenPlatformIsNotSupported()
    {
        $this->setExpectedException('RuntimeException');

        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $platform = $this->getMockBuilder('Doctrine\DBAL\Platforms\AbstractPlatform')->getMockForAbstractClass();

        $conn->expects($this->any())->method('getDatabasePlatform')->will($this->returnValue($platform));

        $p = new DoctrineDbalPlatform($conn);
    }
}
