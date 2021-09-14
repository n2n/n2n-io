<?php
namespace n2n\io;

use n2n\io\fs\FileOperationException;
use PHPUnit\Framework\TestCase;

class IoUtilsTest extends TestCase {
	const TEST_DIR = __DIR__ . 'testFiles';
	const TEST_FILE = self::TEST_DIR . '/' . 'testfile';
	const TEST_FILE_DATA = 'testdata';
	const TEST_IMAGE_DIR = __DIR__ . '/testImages/';
	const TEST_PNG = self::TEST_IMAGE_DIR . '/img.png';
	const TEST_JPEG = self::TEST_IMAGE_DIR . '/img.jpg';
	const TEST_WEBP = self::TEST_IMAGE_DIR . '/img.webp';
	const TEST_GIF = self::TEST_IMAGE_DIR . '/img.gif';

	protected function setUp(): void {
		$this->recursiveRmDir(self::TEST_DIR);
		mkdir(self::TEST_DIR);
		touch(self::TEST_FILE);
		file_put_contents(self::TEST_FILE, self::TEST_FILE_DATA);
	}

	protected function tearDown(): void {
		$this->recursiveRmDir(self::TEST_DIR);
	}

	public function testRename() {
		$oldPath = self::TEST_FILE;
		$newPath = self::TEST_DIR . '/testfileRenamed';
		touch($oldPath);
		IoUtils::rename($oldPath, $newPath);
		$this->assertFalse(is_file($oldPath));
		$this->assertTrue(is_file($newPath));
	}

	public function testRenameFileNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::rename('asdf', 'asdf');
	}

	public function testMkdir() {
		$newDir = self::TEST_DIR . '/dir1/dir2';
		IoUtils::mkdirs($newDir, 777);
		$this->assertTrue(is_dir($newDir));
	}

	public function testRmdir() {
		$dirToDelete = self::TEST_DIR . '/dirToDelete';
		IoUtils::mkdirs($dirToDelete, 777);
		IoUtils::rmdir($dirToDelete);
		$this->assertFalse(is_dir($dirToDelete));
	}

	public function testRmdirNotFound() {
		$this->expectException(FileOperationException::class);
		$dirToDelete = self::TEST_DIR . '/dirToDelete';
		IoUtils::rmdir($dirToDelete);
	}

	public function testRmdirs() {
		$newDir = self::TEST_DIR . '/dir1/dir2';
		IoUtils::mkdirs($newDir, 777);
		IoUtils::rmdirs(self::TEST_DIR);
		$this->assertFalse(is_dir($newDir));
	}

	public function testOpendir() {
		$handle = IoUtils::opendir(self::TEST_DIR);
		$this->assertNotFalse($handle);
	}

	public function testOpendirNotFound() {
		$this->expectException(IoException::class);
		IoUtils::opendir('asdfyxv');
	}

	public function testPutContents() {
		$testData = 'testData';
		$testFile = self::TEST_DIR . '/testFileForPutContents';
		touch($testFile);
		IoUtils::putContents($testFile, $testData);
		$this->assertEquals($testData, file_get_contents($testFile));
	}

	public function testFileGetContents() {
		$contents =  IoUtils::getContents(self::TEST_FILE);
		$this->assertEquals(self::TEST_FILE_DATA, $contents);
	}

	public function testFileGetContentsNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::getContents('asdf');
	}

	public function testFile() {
		$contents = IoUtils::file(self::TEST_FILE);
		$this->assertEquals($contents[0], self::TEST_FILE_DATA);
	}

	public function testFileNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::file('asdf');
	}

	public function testCopy() {
		$targetPath = self::TEST_DIR . '/testFileCopy';
		IoUtils::copy(self::TEST_FILE, $targetPath);
		$this->assertEquals(file(self::TEST_FILE), file($targetPath));
	}

	public function testCopyNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::copy('asdf', 'asdf');
	}

	public function testTouch() {
		$newFile = self::TEST_DIR . '/touchfiletest';
		IoUtils::touch($newFile);
		$this->assertTrue(is_file($newFile));
	}

	public function testFopen() {
		$resource = IoUtils::fopen(self::TEST_FILE, 'r');
		$this->assertTrue(is_resource($resource));
	}

	public function testFopenNotFound() {
		$this->expectException(IoException::class);
		IoUtils::fopen('asdf', 'r');
	}

	public function testStat() {
		$stat = IoUtils::stat(self::TEST_FILE);
		$this->assertTrue(is_array($stat));
	}

	public function testStatNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::stat('asdf');
	}

	public function testFilesize() {
		$filesize = IoUtils::filesize(self::TEST_FILE);
		$this->assertIsNumeric($filesize);
	}

	public function testFilesizeNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::filesize('asdf');
	}

	public function testStreamGetContents() {
		$contents = IoUtils::streamGetContents(fopen(self::TEST_FILE, 'r'));
		$this->assertEquals(self::TEST_FILE_DATA, $contents);
	}

	public function testFilemtime() {
		$filemtime = IoUtils::filemtime(self::TEST_FILE);
		$this->assertIsNumeric($filemtime);
	}

	public function testFilemtimeNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::filemtime('asdf');
	}

	public function testUnlink() {
		IoUtils::unlink(self::TEST_FILE);
		$this->assertFalse(is_file(self::TEST_FILE));
	}

	public function testParseIniString() {
		$parsedIniString = IoUtils::parseIniString('[simple]
				val_one = "some value"
				val_two = 567
			
				[array]
				val_arr[] = "arr_elem_one"
				val_arr[] = "arr_elem_two"
				val_arr[] = "arr_elem_three"
			
				[array_keys]
				val_arr_two[6] = "key_6"
				val_arr_two[some_key] = "some_key_value"');
		$this->assertIsArray($parsedIniString);
		$this->assertEquals(4, count($parsedIniString));
	}

	public function testParseIniFile() {
		file_put_contents(self::TEST_FILE, '[simple]
				val_one = "some value"
				val_two = 567
			
				[array]
				val_arr[] = "arr_elem_one"
				val_arr[] = "arr_elem_two"
				val_arr[] = "arr_elem_three"

				[array_keys]
				val_arr_two[6] = "key_6"
				val_arr_two[some_key] = "some_key_value"');
		$parsedIniFile = IoUtils::parseIniFile(self::TEST_FILE);
		$this->assertIsArray($parsedIniFile);
		$this->assertEquals(4, count($parsedIniFile));
	}

	public function testParseIniFileNotFound() {
		$this->expectException(IoException::class);
		$parsedIniFile = IoUtils::parseIniFile('asdf');
	}

	public function testImageCreateFromPng() {
		$resource = IoUtils::imageCreateFromPng(self::TEST_PNG);
		$this->assertIsResource($resource);
	}

	public function testImageCreateFromPngWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromPng(self::TEST_GIF);
	}

	public function testImageCreateFromGif() {
		$resource = IoUtils::imageCreateFromGif(self::TEST_GIF);
		$this->assertIsResource($resource);
	}

	public function testImageCreateFromGifWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromGif(self::TEST_PNG);
	}

	public function testImageCreateFromJpeg() {
		$resource = IoUtils::imageCreateFromJpeg(self::TEST_JPEG);
		$this->assertIsResource($resource);
	}

	public function testImageCreateFromJpegWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromJpeg(self::TEST_PNG);
	}

	public function testImageCreateFromWebp() {
		$resource = IoUtils::imageCreateFromWebp(self::TEST_WEBP);
		$this->assertIsResource($resource);
	}

	public function testImageCreateFromWebpWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromJpeg(self::TEST_WEBP);
	}

	public function testImagePng() {
		$resource = IoUtils::imagePng(imagecreatefrompng(self::TEST_PNG));
		$this->assertNotFalse($resource);
	}

	public function testImageGif() {
		$resource = IoUtils::imageGif(imagecreatefromgif(self::TEST_GIF));
		$this->assertNotFalse($resource);
	}

	public function testImageJpeg() {
		$resource = IoUtils::imageJpeg(imagecreatefromjpeg(self::TEST_JPEG));
		$this->assertNotFalse($resource);
	}

	public function testImageWebp() {
		$resource = IoUtils::imageWebp(imagecreatefromwebp(self::TEST_WEBP));
		$this->assertNotFalse($resource);
	}

	public function testGetImageSize() {
		$sizeArr = IoUtils::getimagesize(self::TEST_PNG);
		$this->assertIsArray($sizeArr);
	}

	private function recursiveRmDir($dir) {
		if (!is_dir($dir)) return;

		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object)) {
					$this->recursiveRmDir($dir . DIRECTORY_SEPARATOR . $object);
				} else {
					unlink($dir. DIRECTORY_SEPARATOR .$object);
				}
			}
		}
		rmdir($dir);
	}
}