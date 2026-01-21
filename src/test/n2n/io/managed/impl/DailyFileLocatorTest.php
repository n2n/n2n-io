<?php

namespace n2n\io\managed\impl;

use n2n\io\managed\File;
use PHPUnit\Framework\TestCase;

class DailyFileLocatorTest extends TestCase {

	function testBuildDirLevelNamesWithoutPrefix(): void {
		$locator = new DailyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(1, $result);
		$this->assertMatchesRegularExpression('/^\d{4}\d{2}\d{2}$/', $result[0]);
		$this->assertEquals(date('Ymd'), $result[0]);
	}

	function testBuildDirLevelNamesWithSinglePrefix(): void {
		$locator = new DailyDirFileLocator(true, 'imports');
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(2, $result);
		$this->assertEquals('imports', $result[0]);
		$this->assertEquals(date('Ymd'), $result[1]);
	}

	function testBuildDirLevelNamesWithMultiplePrefixes(): void {
		$locator = new DailyDirFileLocator(true, 'uploads', 'images', 'profiles');
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);

		$this->assertCount(4, $result);
		$this->assertEquals('uploads', $result[0]);
		$this->assertEquals('images', $result[1]);
		$this->assertEquals('profiles', $result[2]);
		$this->assertEquals(date('Ymd'), $result[3]);
	}

	function testBuildFileNameReturnsNullWhenUniqueSuffixDisabled(): void {
		$locator = new DailyDirFileLocator(false);
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildFileName($fileMock);

		$this->assertNull($result);
	}

	function testBuildFileNameWithExtension(): void {
		$locator = new DailyDirFileLocator(true);
		$fileMock = $this->createMock(File::class);
		$fileMock->method('getOriginalName')->willReturn('Info.pdf');

		$result = $locator->buildFileName($fileMock);

		$this->assertMatchesRegularExpression('/^Info-[a-z0-9]{7}\.pdf$/', $result);
	}

	function testBuildFileNameWithMultipleExtensions(): void {
		$locator = new DailyDirFileLocator(true);
		$fileMock = $this->createMock(File::class);
		$fileMock->method('getOriginalName')->willReturn('archive.tar.gz');

		$result = $locator->buildFileName($fileMock);

		$this->assertMatchesRegularExpression('/^archive\.tar-[a-z0-9]{7}\.gz$/', $result);
	}

	function testBuildFileNameWithoutExtension(): void {
		$locator = new DailyDirFileLocator(true);
		$fileMock = $this->createMock(File::class);
		$fileMock->method('getOriginalName')->willReturn('README');

		$result = $locator->buildFileName($fileMock);

		$this->assertMatchesRegularExpression('/^README-[a-z0-9]{7}$/', $result);
	}

	function testBuildFileNameGeneratesUniqueTokens(): void {
		$locator = new DailyDirFileLocator(true);
		$fileMock = $this->createMock(File::class);
		$fileMock->method('getOriginalName')->willReturn('test.txt');

		$result1 = $locator->buildFileName($fileMock);
		$result2 = $locator->buildFileName($fileMock);

		$this->assertNotEquals($result1, $result2);
	}

	function testDateFormatIsCorrect(): void {
		$locator = new DailyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result = $locator->buildDirLevelNames($fileMock);
		$datePart = $result[0];

		$parsedDate = \DateTime::createFromFormat('Ymd', $datePart);
		$this->assertNotFalse($parsedDate);
		$this->assertEquals($datePart, $parsedDate->format('Ymd'));
	}

	function testConsecutiveCallsReturnSameDate(): void {
		$locator = new DailyDirFileLocator();
		$fileMock = $this->createMock(File::class);

		$result1 = $locator->buildDirLevelNames($fileMock);
		$result2 = $locator->buildDirLevelNames($fileMock);

		$this->assertEquals($result1[0], $result2[0]);
	}

	function testCreateTokenFileNameStatic(): void {
		$result = DailyDirFileLocator::createTokenFileName('document.pdf');

		$this->assertMatchesRegularExpression('/^document-[a-z0-9]{7}\.pdf$/', $result);
	}
}
