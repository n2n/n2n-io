<?php

namespace n2n\io\img\impl\impl;

use PHPUnit\Framework\TestCase;
use n2n\io\managed\img\impl\ThSt;
use n2n\io\managed\img\ImageMimeType;
use n2n\io\img\impl\ImageSourceFactory;
use n2n\io\img\ImageSource;
use n2n\io\test\IoTestEnv;

class ProportionalThumbStrategyTest extends TestCase {

	function testMatchesMimeType() {
		$jpeg4x2Source = IoTestEnv::createImageSource('12x12.png', ImageMimeType::PNG);

		$this->assertTrue(ThSt::prop(12, 12, imageMimeType: ImageMimeType::PNG)
				->matches($jpeg4x2Source));

		$this->assertFalse(ThSt::prop(12, 12, imageMimeType: ImageMimeType::WEBP)
				->matches($jpeg4x2Source));
	}

	function testMatches() {
		$jpeg4x2Source = IoTestEnv::createImageSource('12x12.png', ImageMimeType::PNG);

		$this->assertTrue(ThSt::prop(12, 12)
				->matches($jpeg4x2Source));

		$this->assertFalse(ThSt::prop(12, 11)
				->matches($jpeg4x2Source));

		$this->assertFalse(ThSt::prop(12, 12, imageMimeType: ImageMimeType::WEBP)
				->matches($jpeg4x2Source));
	}

}