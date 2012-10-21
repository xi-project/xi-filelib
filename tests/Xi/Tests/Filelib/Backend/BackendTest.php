<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\Backend;
use Xi\Filelib\IdentityMap\IdentityMap;

use Xi\Filelib\Backend\Platform\MongoPlatform;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use DateTime;
use Mongo;
use MongoDB;
use MongoId;
use MongoDate;
use MongoConnectionException;
use Xi\Tests\Filelib\TestCase;

class BackendTest extends TestCase
{

    public function setUp()
    {

    }

    /**
     * @test
     */
    public function lus()
    {
        try {
            $mongo = new Mongo(MONGO_DNS, array('connect' => true));
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('Can not connect to MongoDB.');
        }

        // TODO: Fix hard coded db name.
        $this->mongo = $mongo->filelib_tests;

        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $platform = new MongoPlatform($ed, $this->mongo);

        $im = new IdentityMap();

        $backend = new Backend($platform, $im);

        $xoo = $backend->findResource('48a7011a05c677b9a9166101');
        $xoo2 = $backend->findResource('48a7011a05c677b9a9166101');

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $xoo);
        $this->assertInstanceOf('Xi\Filelib\File\Resource', $xoo2);

        $this->assertSame($xoo, $xoo2);


    }



}
