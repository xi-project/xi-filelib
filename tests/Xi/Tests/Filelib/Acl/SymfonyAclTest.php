<?php

namespace Xi\Tests\Filelib\Acl;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Xi\Filelib\Acl\SymfonyAcl;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class SymfonyAclTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var FileLibrary
     */
    private $filelib;

    /**
     *
     * @var SecurityContext
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
        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $foop = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')->disableOriginalConstructor()->getMock();
        $this->fiop = $fiop;
        $this->foop = $foop;

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));
        $filelib->expects($this->any())->method('getFolderOperator')->will($this->returnValue($foop));
        $this->filelib = $filelib;

        $context = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $this->context = $context;

        $this->aclProvider = $this->getMockBuilder('Symfony\Component\Security\Acl\Model\AclProviderInterface')
                                  ->disableOriginalConstructor()
                                  ->getMock();

    }


    /**
     * @test
     */
    public function isFolderBasedShouldRespectConstructorArgument()
    {
        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);
        $this->assertFalse($acl->isFolderBased());

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, true);
        $this->assertTrue($acl->isFolderBased());

    }


    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Acl\SymfonyAcl'));
        $this->assertContains('Xi\Filelib\Acl\Acl', class_implements('Xi\Filelib\Acl\SymfonyAcl'));
    }

    /**
     * @test
     */
    public function classShouldInstantiateCorrectly()
    {
        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider);

        $this->assertSame($this->filelib, $acl->getFilelib());
        $this->assertSame($this->context, $acl->getContext());
        $this->assertSame($this->aclProvider, $acl->getAclProvider());

    }

    /**
     * @test
     */
    public function isFileReadableShouldDelegateFileToSecurityContextWhenFolderBasedIsFalse()
    {
        $file = File::create(array('id' => 1));

        $this->context->expects($this->once())->method('isGranted')
                      ->with($this->equalTo('VIEW'), $this->equalTo($file));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFileReadable($file);

    }

    /**
     * @test
     */
    public function isFileReadableShouldDelegateFolderToSecurityContextWhenFolderBasedIsTrue()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));
        $folder = Folder::create(array('id' => 1));

        $this->foop->expects($this->once())->method('find')->with($this->equalTo(1))
                   ->will($this->returnValue($folder));

        $this->context->expects($this->once())->method('isGranted')
                      ->with($this->equalTo('VIEW'), $this->equalTo($folder));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, true);

        $ret = $acl->isFileReadable($file);

    }


    /**
     * @test
     */
    public function isFileWritableShouldDelegateFileToSecurityContextWhenFolderBasedIsFalse()
    {
        $file = File::create(array('id' => 1));

        $this->context->expects($this->once())->method('isGranted')
                      ->with($this->equalTo('EDIT'), $this->equalTo($file));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFileWritable($file);

    }

    /**
     * @test
     */
    public function isFileWritableShouldDelegateFolderToSecurityContextWhenFolderBasedIsTrue()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));
        $folder = Folder::create(array('id' => 1));

        $this->foop->expects($this->once())->method('find')->with($this->equalTo(1))
                   ->will($this->returnValue($folder));

        $this->context->expects($this->once())->method('isGranted')
                      ->with($this->equalTo('EDIT'), $this->equalTo($folder));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, true);

        $ret = $acl->isFileWritable($file);

    }

    /**
     * @test
     */
    public function isFolderReadableShouldDelegateFolderToSecurityContext()
    {
        $folder = Folder::create(array('id' => 1));

        $this->context->expects($this->once())->method('isGranted')
                      ->with($this->equalTo('VIEW'), $this->equalTo($folder));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFolderReadable($folder);

    }


    /**
     * @test
     */
    public function isFolderWritableShouldDelegateFolderToSecurityContext()
    {
        $folder = Folder::create(array('id' => 1));

        $this->context->expects($this->once())->method('isGranted')
                      ->with($this->equalTo('EDIT'), $this->equalTo($folder));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFolderWritable($folder);

    }

    /**
     * @test
     */
    public function isFileReadableByAnonymousShouldDelegateToFolderWhenAclIsFolderBased()
    {
        $file = File::create(array('id' => 1, 'folder_id' => 1));

        $folder = Folder::create(array('id' => 1));

        $this->foop->expects($this->once())->method('find')->with($this->equalTo(1))
                   ->will($this->returnValue($folder));

        $acl = $this->getMockBuilder('Xi\Filelib\Acl\SymfonyAcl')
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

        $acl = $this->getMockBuilder('Xi\Filelib\Acl\SymfonyAcl')
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

        $acl = $this->getMockBuilder('Xi\Filelib\Acl\SymfonyAcl')
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

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

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

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->anonymousAclQueryWith($file);

    }




    /**
     * @xtest
     */
    public function isFolderReadableByAnonymousShouldReturnFalse()
    {
        $folder = Folder::create(array('id' => 1));

        $acl = new SymfonyAcl($this->filelib, $this->context, $this->aclProvider, false);

        $ret = $acl->isFolderReadableByAnonymous($folder);

        $this->assertFalse($ret);
    }


}
