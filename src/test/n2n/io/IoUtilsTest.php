<?php
namespace n2n\io;

use PHPUnit\Framework\TestCase;

class IoUtilsTest extends TestCase {
	const TEST_DIR_NAME = 'testFiles';

	protected function setUp(): void {
		$this->recursiveRmDir(self::TEST_DIR_NAME);
		mkdir(self::TEST_DIR_NAME);
	}

	protected function tearDown(): void {
		$this->recursiveRmDir(self::TEST_DIR_NAME);
	}

	public function testRename() {
		$oldPath = self::TEST_DIR_NAME . '/testfile';
		$newPath = self::TEST_DIR_NAME . '/testfileRenamed';
		touch($oldPath);
		IoUtils::rename($oldPath, $newPath);
		$this->assertFalse(is_file($oldPath));
		$this->assertTrue(is_file($newPath));
	}

	public function testMkdir() {
		$newDir = self::TEST_DIR_NAME . '/' . self::TEST_DIR_NAME . '/' . self::TEST_DIR_NAME;
		IoUtils::mkdirs($newDir, 0777);
		$this->assertTrue(is_dir($newDir));
	}

	public function testRmdir() {
		IoUtils::rmdir(self::TEST_DIR_NAME);
		$this->assertFalse(self::TEST_DIR_NAME);
	}

	public function testRmdirs() {

	}

	public function testOpendir() {

	}

	public function testFilePutContents() {

	}

	public function testFileGetContents() {

	}

	public function testFile() {

	}

	public function testCopy() {

	}

	public function testChmod() {

	}

	public function testTouch() {

	}

	public function testFopen() {

	}

	public function testStat() {

	}

	public function testFilesize() {

	}

	public function testStreamGetContents() {

	}

	public function testFilemtime() {

	}

	public function testUnlink() {

	}

	public function testParseIniString() {

	}

	public function testParseIniFile() {

	}

	public function testImageCreateFromPng() {

	}

	public function testImageCreateFromGif() {

	}

	public function testImageCreateFromJpeg() {

	}

	public function testImageCreateFromWebp() {

	}

	public function testImagePng() {

	}

	public function testImageGif() {

	}

	public function testImageJpeg() {

	}

	public function testImageWebp() {

	}

	public function testGetImageSize() {

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