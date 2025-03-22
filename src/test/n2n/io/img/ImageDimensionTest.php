<?php

namespace n2n\io\img;

use PHPUnit\Framework\TestCase;
use n2n\io\managed\img\ImageDimension;
use n2n\io\managed\img\ImageMimeType;

class ImageDimensionTest extends TestCase {

	function testFull(): void {
		$imageDimension = new ImageDimension(321, 123, true, true, 'some-id',
				ImageMimeType::WEBP);

		$imageDimensionStr = (string) $imageDimension;
		$this->assertEquals('321x123xccenters4xsome-id', $imageDimensionStr);

		$this->assertEquals($imageDimension, ImageDimension::createFromString($imageDimensionStr));
	}

	function testLess(): void {
		$imageDimension = new ImageDimension(321, 123, false, false);

		$imageDimensionStr = (string) $imageDimension;
		$this->assertEquals('321x123', $imageDimensionStr);

		$this->assertEquals($imageDimension, ImageDimension::createFromString($imageDimensionStr));
	}

	function testMimeType(): void {
		$imageDimension = new ImageDimension(321, 123, false, false,
				mimeType: ImageMimeType::WEBP);

		$imageDimensionStr = (string) $imageDimension;
		$this->assertEquals('321x123x4', $imageDimensionStr);

		$this->assertEquals($imageDimension, ImageDimension::createFromString($imageDimensionStr));
	}

	function testIdExt(): void {
		$imageDimension = new ImageDimension(321, 123, false, false,
				'some-id');

		$imageDimensionStr = (string) $imageDimension;
		$this->assertEquals('321x123xxsome-id', $imageDimensionStr);

		$this->assertEquals($imageDimension, ImageDimension::createFromString($imageDimensionStr));
	}

}