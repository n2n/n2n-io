<?php

namespace n2n\io\managed\val;

use PHPUnit\Framework\TestCase;
use n2n\io\managed\File;
use n2n\io\managed\impl\engine\tmp\TmpFileEngine;
use n2n\util\io\fs\FsPath;
use n2n\util\io\IoUtils;
use n2n\util\io\fs\FileOperationException;

class FileValidationUtilsTest extends TestCase {

	private TmpFileEngine $tmpFileEngine;
	private FsPath $testDir;
	private FsPath $sessionTestDir;

	/**
	 * @throws FileOperationException
	 */
	function setUp(): void {
		$id = uniqid();
		$this->testDir = new FsPath(__DIR__ . DIRECTORY_SEPARATOR . 'dump' . DIRECTORY_SEPARATOR . $id);
		$this->testDir->mkdirs();
		$this->sessionTestDir = new FsPath(__DIR__ . DIRECTORY_SEPARATOR . 'sessdump' . DIRECTORY_SEPARATOR . $id);
		$this->sessionTestDir->mkdirs();
		$this->tmpFileEngine = new TmpFileEngine($this->testDir, $this->sessionTestDir, '0777', '0777', 'tmp' . $id);
	}

	function tearDown(): void {
		if ($this->testDir->exists()) {
			$this->testDir->delete();
		}
		if ($this->sessionTestDir->exists()) {
			$this->sessionTestDir->delete();
		}
	}

	private function createFileWithSize(int $size): File {
		$file = $this->tmpFileEngine->createFile();
		$content = str_repeat('x', $size);
		IoUtils::putContents($file->getFileSource()->getFsPath(), $content);
		return $file;
	}
	
	function testEmptySizeAllowed(): void {
		$file = $this->createFileWithSize(0);
		$this->assertTrue(FileValidationUtils::sizeAllowed($file, 0));
		$file->delete();
	}

	function testSize2048Allowed() {
		$file = $this->createFileWithSize(2048);
		$this->assertTrue(FileValidationUtils::sizeAllowed($file, 2048));
		$file->delete();
	}

	function testSizeTooBig() {
		$file = $this->createFileWithSize(2048);
		$this->assertFalse(FileValidationUtils::sizeAllowed($file, 2047));
		$file->delete();
	}
}