<?php

namespace n2n\io\managed\impl;

use n2n\io\managed\File;
use n2n\io\managed\FileLocator;
use n2n\util\HashUtils;

/**
 * FileLocator that organizes files into date-based folders (e.g., 20250401/).
 * 
 * The date is captured at the time of file persist (upload), creating a directory structure
 * with up to 365 folders per year. This locator only affects storage location - file retrieval
 * uses the qualified name stored in the database.
 */
class DailyDirFileLocator implements FileLocator {

	private const TOKEN_LENGTH = 7;

	private bool $uniqueSuffix;
	private array $prefixLevels;

	/**
	 * @param bool $uniqueSuffix Whether to append a unique token to filenames (e.g., Info.pdf -> Info-a8f3k2x.pdf)
	 * @param string ...$prefixLevels Optional prefix directories before the date folder (e.g., 'imports', 'exports')
	 */
	function __construct(bool $uniqueSuffix = true, string ...$prefixLevels) {
		$this->uniqueSuffix = $uniqueSuffix;
		$this->prefixLevels = $prefixLevels;
	}

	function buildDirLevelNames(File $file): array {
		return [...$this->prefixLevels, date('Ymd')];
	}

	function buildFileName(File $file): ?string {
		if (!$this->uniqueSuffix) {
			return null;
		}

		return self::createTokenFileName($file->getOriginalName());
	}

	static function createTokenFileName(string $originalName): string {
		$token = HashUtils::base36Md5Hash(uniqid('', true), self::TOKEN_LENGTH);

		$dotPos = strrpos($originalName, '.');
		if ($dotPos === false) {
			return $originalName . '-' . $token;
		}

		$baseName = substr($originalName, 0, $dotPos);
		$extension = substr($originalName, $dotPos + 1);

		return $baseName . '-' . $token . '.' . $extension;
	}
}
