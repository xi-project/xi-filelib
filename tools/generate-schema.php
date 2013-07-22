<?php

require __DIR__ . '/../tests/bootstrap.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
class SchemaGenerator
{
    /**
     * @var array
     */
    private $connectionOptions;

    /**
     * @param  array           $connectionOptions
     * @return SchemaGenerator
     */
    public function __construct(array $connectionOptions)
    {
        $this->connectionOptions = $connectionOptions;
    }

    /**
     * Generate SQL for creating schema
     *
     * @return string
     */
    public function generate()
    {
        AnnotationRegistry::registerFile(
            __DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        $driver = new AnnotationDriver(
            new CachedReader(new AnnotationReader(), new ArrayCache()),
            array(
                __DIR__ . '/../library/Xi/Filelib/Backend/Platform/DoctrineOrm/Entity',
            )
        );

        $config = new Configuration();
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(ROOT_TESTS . '/data/temp');
        $config->setProxyNamespace('Proxies');

        $em = EntityManager::create($this->connectionOptions, $config);

        $st = new SchemaTool($em);
        $metadata = $st->getCreateSchemaSql($em->getMetadataFactory()->getAllMetadata());

        return join(";\n", $metadata) . ";\n";
    }
}

if ($argc < 2) {
    echo <<<EOT
usage: php $argv[0] <driver>

example: php $argv[0] sqlite
example: php $argv[0] mysql
example: php $argv[0] pgsql

EOT;

    die;
}

$options['driver'] = 'pdo_' . $argv[1];
$options['host'] = '127.0.0.1';
// $options['username'] = 'michalis-rakintzis';
// $options['password'] = 'sagapo';
// $options['dbname'] = 'dbname';

$generator = new SchemaGenerator($options);

echo $generator->generate();
