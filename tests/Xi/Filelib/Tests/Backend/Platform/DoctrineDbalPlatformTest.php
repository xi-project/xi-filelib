<?php

namespace Xi\Filelib\Tests\Backend\Platform;

use Xi\Filelib\Backend\Platform\DoctrineDbalPlatform;
use Doctrine\DBAL\DriverManager;

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
}
