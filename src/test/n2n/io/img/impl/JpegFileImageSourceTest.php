<?php

namespace n2n\io\img\impl;

use PHPUnit\Framework\TestCase;
use n2n\io\test\IoTestEnv;
use n2n\io\managed\img\ImageMimeType;

class JpegFileImageSourceTest extends TestCase {

	function testCreateImageResource() {
		$source = IoTestEnv::createImageSource('4x2.jpg', ImageMimeType::JPEG);
		$this->assertInstanceOf(JpegFileImageSource::class, $source);
		$image = $source->createImageResource();

		$this->assertEquals(4, $image->getWidth());
		$this->assertEquals(2, $image->getHeight());

		$image->destroy();
	}

	public function testCreateImageResourceWithExif() {
		$source = IoTestEnv::createImageSource('4x2-exif-orientation-6.jpg', ImageMimeType::JPEG);
		$this->assertInstanceOf(JpegFileImageSource::class, $source);
		$image = $source->createImageResource();

		$this->assertEquals(2, $image->getWidth());
		$this->assertEquals(4, $image->getHeight());

		$image->destroy();

	}
}