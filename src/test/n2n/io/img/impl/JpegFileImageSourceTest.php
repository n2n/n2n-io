<?php

namespace n2n\io\img\impl;

use PHPUnit\Framework\TestCase;

class JpegFileImageSourceTest extends TestCase {




	function testCreateImageResource() {
		$source = new JpegFileImageSource(__DIR__ . '/testimages/4x2.jpg');
		$image = $source->createImageResource();

		$this->assertEquals(4, $image->getWidth());
		$this->assertEquals(2, $image->getHeight());

		$image->destroy();

	}

	public function testCreateImageResourceWithExif() {
		$source = new JpegFileImageSource(__DIR__ . '/testimages/4x2-exif-orientation-6.jpg');
		$image = $source->createImageResource();

		$this->assertEquals(2, $image->getWidth());
		$this->assertEquals(4, $image->getHeight());

		$image->destroy();

	}
}