<?php

namespace Xi\Filelib\Tests\Authorization;

use \PHPUnit_Framework_TestCase as TestCase;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Authorization\Adapter\SimpleAuthorizationAdapter;

class SimpleAuthorizationAdapterTest extends \Xi\Filelib\Tests\TestCase
{

    public function provideMethods()
    {
        return array(
            array('fileReadableByAnonymous'),
            array('fileReadable'),
            array('fileWritable'),
            array('folderReadableByAnonymous'),
            array('folderReadable'),
            array('folderWritable'),
        );
    }


    /**
     * @test
     * @dataProvider provideMethods
     */
    public function shouldBeConfigurable($method)
    {
        $setter = 'set' . ucfirst($method);
        $getter = 'is' . ucfirst($method);

        if (preg_match('#^file#', $method)) {
            $identifiable = $this->getMockedFile();
        } else {
            $identifiable = $this->getMockedFolder();
        }

        $acl = new SimpleAuthorizationAdapter();
        $this->assertTrue($acl->$getter($identifiable));

        $this->assertSame($acl, $acl->$setter(false));
        $this->assertFalse($acl->$getter($identifiable));

    }



}
