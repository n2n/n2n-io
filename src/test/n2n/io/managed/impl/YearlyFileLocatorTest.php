<?php

namespace n2n\io\managed\impl;

use n2n\io\managed\File;
use PHPUnit\Framework\TestCase;

class YearlyFileLocatorTest extends TestCase {

	function testBuildDirLevelNamesWithoutPrefix(): void {
		$locator = new YearlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(1, $result);
		$this->assertMatchesRegularExpression('/^\d{4}$/', $result[0]);
		$this->assertEquals(date('Y'), $result[0]);
	}

	function testBuildDirLevelNamesWithSinglePrefix(): void {
		$locator = new YearlyDirFileLocator(true, 'imports');
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(2, $result);
		$this->assertEquals('imports', $result[0]);
		$this->assertEquals(date('Y'), $result[1]);
	}

	function testBuildDirLevelNamesWithMultiplePrefixes(): void {
		$locator = new YearlyDirFileLocator(true, 'uploads', 'images', 'profiles');
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(4, $result);
		$this->assertEquals('uploads', $result[0]);
		$this->assertEquals('images', $result[1]);
		$this->assertEquals('profiles', $result[2]);
		$this->assertEquals(date('Y'), $result[3]);
	}

	function testBuildFileNameReturnsNullWhenUniqueSuffixDisabled(): void {
		$locator = new YearlyDirFileLocator(false);
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildFileName($fileMock);

		$this->assertNull($result);
	}

	function testBuildFileNameWithExtension(): void {
		$locator = new YearlyDirFileLocator(true);
		$fileMock = $this->createMock(File::class);
		$fileMock->method('getOriginalName')->willReturn('Photo.jpg');

		$result = $locator->buildFileName($fileMock);

		$this->assertMatchesRegularExpression('/^Photo-[a-z0-9]{7}\.jpg$/', $result);
	}

	function testBuildFileNameGeneratesUniqueTokens(): void {
		$locator = new YearlyDirFileLocator(true);
		$fileMock = $this->createMock(File::class);
		$fileMock->method('getOriginalName')->willReturn('test.txt');

		$result1 = $locator->buildFileName($fileMock);
		$result2 = $locator->buildFileName($fileMock);

		$this->assertNotEquals($result1, $result2);
	}

	function testDateFormatIsCorrect(): void {
		$locator = new YearlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);
		$datePart = $result[0];

		$parsedDate = \DateTime::createFromFormat('Y', $datePart);
		$this->assertNotFalse($parsedDate);
		$this->assertEquals($datePart, $parsedDate->format('Y'));
	}

	function testConsecutiveCallsReturnSameDate(): void {
		$locator = new YearlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result1 = $locator->buildDirLevelNames($fileMock);
		$result2 = $locator->buildDirLevelNames($fileMock);

		$this->assertEquals($result1[0], $result2[0]);
	}
}
