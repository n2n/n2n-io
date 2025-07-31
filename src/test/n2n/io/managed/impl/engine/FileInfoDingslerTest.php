<?php
namespace n2n\io\managed\impl\engine;

use PHPUnit\Framework\TestCase;
use n2n\util\io\fs\FsPath;
use n2n\util\io\IoUtils;
use n2n\io\managed\FileInfo;

class FileInfoDingslerTest extends TestCase {

	private FsPath $tempDirPath;
	private FsPath $testFilePath;
	private FileInfoDingsler $fileInfoDingsler;

	protected function setUp(): void {
		$this->tempDirPath = new FsPath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'FileInfoDingslerTest_' . uniqid());
		$this->tempDirPath->mkdirs();

		$this->testFilePath = $this->tempDirPath->ext('test-file.pdf');
		$this->testFilePath->touch();
		IoUtils::putContents($this->testFilePath, '%PDF-1.4 test content');

		$this->fileInfoDingsler = new FileInfoDingsler($this->testFilePath);
	}

	protected function tearDown(): void {
		if ($this->tempDirPath->exists()) {
			$this->tempDirPath->delete();
		}
	}

	public function testReadWithoutInfoFiles(): void {
		$this->assertFalse($this->fileInfoDingsler->exists());
		$this->assertFalse(file_exists($this->testFilePath . '.privinf'));

		$fileInfo = $this->fileInfoDingsler->read();

		$this->assertInstanceOf(FileInfo::class, $fileInfo);
		$this->assertNull($fileInfo->getOriginalName());
	}

	public function testReadWithInfoFile(): void {
		$originalFileInfo = new FileInfo('original-document.pdf');
		$this->fileInfoDingsler->write($originalFileInfo);

		$this->assertTrue($this->fileInfoDingsler->exists());

		$readFileInfo = $this->fileInfoDingsler->read();

		$this->assertInstanceOf(FileInfo::class, $readFileInfo);
		$this->assertEquals('original-document.pdf', $readFileInfo->getOriginalName());
	}

	public function testReadWithPrivinfFile(): void {
		$this->assertFalse($this->fileInfoDingsler->exists());

		$privinfPath = $this->testFilePath . '.privinf';
		file_put_contents($privinfPath, 'legacy-filename.pdf');

		$fileInfo = $this->fileInfoDingsler->read();

		$this->assertInstanceOf(FileInfo::class, $fileInfo);
		$this->assertEquals('legacy-filename.pdf', $fileInfo->getOriginalName());

		unlink($privinfPath);
	}

	public function testNoWarningsWhenNoFileExists(): void {
		$warningCount = 0;

		set_error_handler(function($severity) use (&$warningCount) {
			if ($severity === E_WARNING) {
				$warningCount++;
			}
			return false;
		});

		$this->assertFalse(file_exists($this->testFilePath . '.privinf'));
		$this->fileInfoDingsler->read();
		$this->assertEquals(0, $warningCount);
	}
}