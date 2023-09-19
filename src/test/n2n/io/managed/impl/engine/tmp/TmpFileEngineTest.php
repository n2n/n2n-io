<?php

namespace n2n\io\managed\impl\engine\tmp;

use PHPUnit\Framework\TestCase;
use n2n\util\io\fs\FsPath;
use n2n\util\io\IoUtils;
use n2n\io\managed\img\ImageFile;
use n2n\io\managed\img\impl\ThSt;

class TmpFileEngineTest extends TestCase {

	private TmpFileEngine $tmpFileEngine;

	function setUp(): void {
		$id = uniqid();
		$dirPath = new FsPath(__DIR__ . DIRECTORY_SEPARATOR . 'dump' . DIRECTORY_SEPARATOR . $id);
		$dirPath->mkdirs();
		$this->tmpFileEngine = new TmpFileEngine($dirPath, $dirPath, '0777', '0777', 'tmp' . $id);
	}

	function testAffiliation() {
		$file = $this->tmpFileEngine->createFile();
		IoUtils::putContents($file->getFileSource()->getFsPath(), IoUtils::getContents(__DIR__ . '/image.png'));

		$affiliationEngine = $file->getFileSource()->getAffiliationEngine();

		$varFileSource = $affiliationEngine->getVariationManager()->create('superduper');
		$varFsPath = $varFileSource->getFsPath();

		$thumbFsPath = (new ImageFile($file))->getOrCreateThumb(ThSt::prop(1, 1))->getFile()->getFileSource()->getFsPath();

		$this->assertTrue($file->isValid());
		$this->assertTrue($varFsPath->exists());
		$this->assertTrue($thumbFsPath->exists());

		$file->delete();

		$this->assertFalse($file->isValid());
		$this->assertFalse($varFsPath->exists());
		$this->assertFalse($thumbFsPath->exists());
	}


	function testLeftover() {
		$file = $this->tmpFileEngine->createFile();
		$fsPath = $file->getFileSource()->getFsPath();

		$varFsPath = $fsPath->getParent()->ext('res-var-superduper')->ext($fsPath->getName());
		$varFsPath->mkdirsAndCreateFile('0777', '0666');

		$thumbFsPath = $fsPath->getParent()->ext('res-1x1xs')->ext($fsPath->getName());
		$thumbFsPath->mkdirsAndCreateFile('0777', '0666');


		$this->assertTrue($file->isValid());
		$this->assertTrue($varFsPath->exists());
		$this->assertTrue($thumbFsPath->exists());

		$file->delete();

		$this->assertFalse($file->isValid());
		$this->assertFalse($varFsPath->exists());
		$this->assertFalse($thumbFsPath->exists());
	}
}