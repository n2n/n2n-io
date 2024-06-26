<?php

namespace n2n\io\managed\impl\engine\transactional;

use PHPUnit\Framework\TestCase;
use n2n\util\io\fs\FsPath;
use n2n\io\managed\impl\engine\FileInfoDingsler;
use n2n\util\io\IoUtils;
use n2n\io\managed\FileManagingException;

class TransactionalFileEngineTest extends TestCase {

	private FsPath $dirPath;
	private TransactionalFileEngine $transactionalFileEngine;

	function setUp(): void {

		$id = uniqid();
		$this->dirPath = new FsPath(__DIR__ . DIRECTORY_SEPARATOR . 'dump' . DIRECTORY_SEPARATOR . $id);
		$this->dirPath->mkdirs();
		$this->transactionalFileEngine = new TransactionalFileEngine('transactional' . $id, $this->dirPath, '0777', '0777', );
	}

	function testFileNotExists(): void {

		$this->assertNull($this->transactionalFileEngine->getByQualifiedName('huii.txt', true));
		$this->assertNull($this->transactionalFileEngine->getByQualifiedName('huii.txt'));

		$file = $this->transactionalFileEngine->getByQualifiedName('huii.txt', false);
		$this->assertNotNull($file);
		$this->assertFalse($file->isValid());

	}

	function testCustomFileNamesExists(): void {
		$this->transactionalFileEngine->setCustomFileNamesAllowed(true);
		$fileFsPath = $this->dirPath->ext('huii.txt');
		$fileFsPath->touch();

		$file = $this->transactionalFileEngine->getByQualifiedName('huii.txt', true);
		$this->assertNotNull($file);
		$this->assertTrue($file->isValid());
		$this->assertEquals('huii.txt', $file->getOriginalName());
		$this->assertEquals('txt', $file->getOriginalExtension());

		$fileFsPath->delete();
		$this->assertFalse($fileFsPath->exists());

		$file2 = $this->transactionalFileEngine->getByQualifiedName('huii.txt', false);
		$this->assertTrue($file !== $file2);
		$this->assertNotNull($file2);
		$fileFsPath->touch();
		$this->assertTrue($file2->isValid());
		$this->assertEquals('huii.txt', $file2->getOriginalName());
		$this->assertEquals('txt', $file->getOriginalExtension());

		$fileFsPath->delete();
		$this->assertTrue($file2->isValid());
	}


	function testPrivateFileNamesExists(): void {
		$this->dirPath->ext('huii.txt')->touch();
		$infoFsPath = $this->dirPath->ext('huii.txt' . FileInfoDingsler::INFO_SUFFIX);
		$infoFsPath->touch();
		IoUtils::putContents($infoFsPath, json_encode(['originalName' => 'holeradio.txt']));

		$file = $this->transactionalFileEngine->getByQualifiedName('huii.txt');
		$this->assertNotNull($file);
		$this->assertTrue($file->isValid());
		$this->assertEquals('holeradio.txt', $file->getOriginalName());
		$this->assertEquals('txt', $file->getOriginalExtension());

		$infoFsPath->delete();

		$file2 = $this->transactionalFileEngine->getByQualifiedName('huii.txt');
		$this->assertTrue($file !== $file2);
		$this->assertNotNull($file2);
		$this->assertTrue($file2->isValid());

		$this->expectException(FileManagingException::class);
		try {
			$file2->getOriginalName();
		} finally {
			// currently the file remains valid because this Exception does no longer cause the file to be deleted.
//			$this->assertFalse($file2->isValid());
			// currently the file remains because this Exception does no longer cause the file to be deleted.
//			$this->assertNull($this->transactionalFileEngine->getByQualifiedName('huii.txt'));
		}
	}
}