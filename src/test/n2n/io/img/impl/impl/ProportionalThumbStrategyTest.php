<?php

namespace n2n\io\img\impl\impl;

use PHPUnit\Framework\TestCase;
use n2n\io\managed\img\impl\ThSt;
use n2n\io\managed\img\ImageMimeType;
use n2n\io\img\impl\ImageSourceFactory;
use n2n\io\img\ImageSource;
use n2n\io\test\IoTestEnv;

class ProportionalThumbStrategyTest extends TestCase {

	function testMimeType() {
		$jpeg4x2Source = IoTestEnv::createImageSource('4x2.jpg', ImageMimeType::WEBP);

		$this->assertFalse(ThSt::prop(4, 2, imageMimeType: ImageMimeType::JPEG)
				->matches($jpeg4x2Source));

		$this->assertTrue(ThSt::prop(4, 2, imageMimeType: ImageMimeType::WEBP)
				->matches($jpeg4x2Source));
	}

}