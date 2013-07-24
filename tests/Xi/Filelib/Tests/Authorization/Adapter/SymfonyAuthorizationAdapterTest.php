<?php

namespace Xi\Filelib\Tests\Authorization;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Xi\Filelib\Authorization\Adapter\SymfonyAuthorizationAdapter;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class SymfonyAuthorizationAdapterTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     *
     * @var FileLibrary
     */
    private $filelib;

    /**
     *
     * @var SecurityContextInterface
     */
    private $context;

    /**
     *
     * @var FileOperator
     */
    private $fiop;

    /**
     *
     * @var FolderOperator
     */
    private $foop;

    /**
     *
     * @var AclProviderInterface
     */
    private $aclProvider;

    public function setUp()
    {
        $this->fiop = $this->getMockedFileOperator();
        $this->foop = $this->getMockedFolderOperator();

        $this->filelib = $this->getMockedFilelib(null, $this->fiop, $this->foop);

        $context = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $context;

        $this->aclProvider = $this
            ->getMockBuilder('Symfony\Component\Security\Acl\Model\AclProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function isFolderBasedShouldRespectConstructorArgument()
    {
        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);
        $this->assertFalse($acl->isFolderBased());

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, true);
        $this->assertTrue($acl->isFolderBased());

    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertImplements('Xi\Filelib\Authorization\AuthorizationAdapter', 'Xi\Filelib\Authorization\Adapter\SymfonyAuthorizationAdapter');
    }

    /**
     * @test
     */
    public function isFileReadableShouldDelegateFileToSecurityContextWhenFolderBasedIsFalse()
    {
        $file = File::create(array('id' => 1));

        $this->contextExpect($file, 'VIEW', true);

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFileReadable($file);
        $this->assertTrue($ret);

    }

    /**
     * @test
     */
    public function isFileReadableShouldDelegateFolderToSecurityContextWhenFolderBasedIsTrue()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));
        $folder = Folder::create(array('id' => 1));

        $this->foop
            ->expects($this->once())
            ->method('find')->with($this->equalTo(1))
            ->will($this->returnValue($folder));

        $this->contextExpect($folder, 'VIEW', false);
        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, true);

        $ret = $acl->isFileReadable($file);
        $this->assertFalse($ret);

    }

    /**
     * @test
     */
    public function isFileWritableShouldDelegateFileToSecurityContextWhenFolderBasedIsFalse()
    {
        $file = File::create(array('id' => 1));

        $this->contextExpect($file, 'EDIT', true);
        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFileWritable($file);
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function isFileWritableShouldDelegateFolderToSecurityContextWhenFolderBasedIsTrue()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));
        $folder = Folder::create(array('id' => 1));

        $this->foop
            ->expects($this->once())
            ->method('find')->with($this->equalTo(1))
            ->will($this->returnValue($folder));


        $this->contextExpect($folder, 'EDIT', true);

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, true);

        $ret = $acl->isFileWritable($file);
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function isFolderReadableShouldDelegateFolderToSecurityContext()
    {
        $folder = Folder::create(array('id' => 1));

        $this->contextExpect($folder, 'VIEW', true);

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFolderReadable($folder);
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function isFolderWritableShouldDelegateFolderToSecurityContext()
    {
        $folder = Folder::create(array('id' => 1));

        $this->contextExpect($folder, 'EDIT', false);

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFolderWritable($folder);
        $this->assertFalse($ret);
    }

    /**
     * @test
     */
    public function isFileReadableByAnonymousShouldDelegateToFolderWhenAclIsFolderBased()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));

        $folder = Folder::create(array('id' => 1));

        $this->foop
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue($folder));

        $acl = $this->getMockBuilder('Xi\Filelib\Authorization\Adapter\SymfonyAuthorizationAdapter')
                    ->setConstructorArgs(array($this->filelib, $this->context, $this->aclProvider, true))
                    ->setMethods(array('isFolderReadableByAnonymous'))
                    ->getMock();

        $acl->expects($this->once())->method('isFolderReadableByAnonymous')->with($this->equalTo($folder));

        $this->assertTrue($acl->isFolderBased());

        $ret = $acl->isFileReadableByAnonymous($file);

    }

    /**
     * @test
     */
    public function isFileReadableByAnonymousShouldDelegateToAclWhenAclIsNotFolderBased()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));

        $acl = $this->getMockBuilder('Xi\Filelib\Authorization\Adapter\SymfonyAuthorizationAdapter')
                    ->setConstructorArgs(array($this->filelib, $this->context, $this->aclProvider, false))
                    ->setMethods(array('anonymousAclQueryWith'))
                    ->getMock();

        $acl->expects($this->once())->method('anonymousAclQueryWith')->with($this->equalTo($file));

        $this->assertFalse($acl->isFolderBased());
        $ret = $acl->isFileReadableByAnonymous($file);

    }

    /**
     * @test
     */
    public function isFolderReadableByAnonymousShouldDelegateToAcl()
    {
        $folder = Folder::create(array('id' => 1));

        $acl = $this->getMockBuilder('Xi\Filelib\Authorization\Adapter\SymfonyAuthorizationAdapter')
                    ->setConstructorArgs(array($this->filelib, $this->context, $this->aclProvider, false))
                    ->setMethods(array('anonymousAclQueryWith'))
                    ->getMock();

        $acl->expects($this->once())->method('anonymousAclQueryWith')->with($this->equalTo($folder));

        $ret = $acl->isFolderReadableByAnonymous($folder);

    }

    /**
     * @test
     */
    public function anonymousAclQueryShouldReturnFalseWhenAclIsNotFoundForObject()
    {
        $file = File::create(array('id' => 1));

        $this->aclProvider->expects($this->once())->method('findAcl')
                          ->with($this->isInstanceOf('Symfony\Component\Security\Acl\Domain\ObjectIdentity'))
                          ->will($this->throwException(new AclNotFoundException('Xooxoo')));

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->anonymousAclQueryWith($file);

        $this->assertFalse($ret);

    }

    /**
     * @test
     */
    public function anonymousAclQueryShouldDelegateToIsGrantedWhenAclIsFound()
    {
        $file = File::create(array('id' => 1));

        $acl = $this->getMockForAbstractClass('Symfony\Component\Security\Acl\Model\AclInterface');

        $acl->expects($this->once())->method('isGranted')->with(
            $this->equalTo(array(MaskBuilder::MASK_VIEW))
        );

        $this->aclProvider->expects($this->once())->method('findAcl')
                          ->with($this->isInstanceOf('Symfony\Component\Security\Acl\Domain\ObjectIdentity'))
                          ->will($this->returnValue($acl));

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->anonymousAclQueryWith($file);

    }

    /**
     * @xtest
     */
    public function isFolderReadableByAnonymousShouldReturnFalse()
    {
        $folder = Folder::create(array('id' => 1));

        $acl = new SymfonyAuthorizationAdapter($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFolderReadableByAnonymous($folder);

        $this->assertFalse($ret);
    }



    /**
     * @param $withWhat
     * @param $permission
     * @param $returnValue
     */
    protected function contextExpect($withWhat, $permission, $returnValue)
    {
        $this->context
            ->expects($this->once())->method('isGranted')
            ->with($this->equalTo($permission), $this->equalTo($withWhat))
            ->will($this->returnValue($returnValue));
    }



}
