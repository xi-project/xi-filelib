<?php

namespace Xi\Filelib\Tests\Authorization;

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
    public function shouldBeSimplyConfigurable($method)
    {
        $setter = 'set' . ucfirst($method);
        $getter = 'is' . ucfirst($method);

        if (preg_match('#^file#', $method)) {
            $identifiable = $this->getMockedFile();
        } else {
            $identifiable = $this->getMockedFolder();
        }

        $acl = new SimpleAuthorizationAdapter();
        $acl->attachTo($this->getMockedFilelib());
        $this->assertTrue($acl->$getter($identifiable));

        $this->assertSame($acl, $acl->$setter(false));
        $this->assertFalse($acl->$getter($identifiable));

    }

    /**
     * @test
     */
    public function shouldBeConfigurableWithAClosure()
    {
        $file1 = $this->getMockedFile();
        $file2 = $this->getMockedFile();

        $func = function(File $file) use ($file1, $file2) {
            if ($file === $file1) {
                return true;
            }
            return false;
        };


        $acl = new SimpleAuthorizationAdapter();
        $acl->setFileReadableByAnonymous($func);

        $this->assertTrue($acl->isFileReadableByAnonymous($file1));
        $this->assertFalse($acl->isFileReadableByAnonymous($file2));
    }
}
