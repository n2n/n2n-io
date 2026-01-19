<?php

namespace n2n\io\managed\impl;

use n2n\io\managed\File;
use PHPUnit\Framework\TestCase;

class MonthlyFileLocatorTest extends TestCase {

	function testBuildDirLevelNamesWithoutPrefix(): void {
		$locator = new MonthlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(1, $result);
		$this->assertMatchesRegularExpression('/^\d{4}\d{2}$/', $result[0]);
		$this->assertEquals(date('Ym'), $result[0]);
	}

	function testBuildDirLevelNamesWithSinglePrefix(): void {
		$locator = new MonthlyDirFileLocator('imports');
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(2, $result);
		$this->assertEquals('imports', $result[0]);
		$this->assertEquals(date('Ym'), $result[1]);
	}

	function testBuildDirLevelNamesWithMultiplePrefixes(): void {
		$locator = new MonthlyDirFileLocator('uploads', 'images', 'profiles');
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(4, $result);
		$this->assertEquals('uploads', $result[0]);
		$this->assertEquals('images', $result[1]);
		$this->assertEquals('profiles', $result[2]);
		$this->assertEquals(date('Ym'), $result[3]);
	}

	function testBuildFileNameReturnsNull(): void {
		$locator = new MonthlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildFileName($fileMock);

		$this->assertNull($result);
	}

	function testDateFormatIsCorrect(): void {
		$locator = new MonthlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);
		$datePart = $result[0];

		$parsedDate = \DateTime::createFromFormat('Ym', $datePart);
		$this->assertNotFalse($parsedDate);
		$this->assertEquals($datePart, $parsedDate->format('Ym'));
	}

	function testConsecutiveCallsReturnSameDate(): void {
		$locator = new MonthlyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result1 = $locator->buildDirLevelNames($fileMock);
		$result2 = $locator->buildDirLevelNames($fileMock);

		$this->assertEquals($result1[0], $result2[0]);
	}
}
