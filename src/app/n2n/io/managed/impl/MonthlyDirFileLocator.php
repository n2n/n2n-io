<?php

namespace n2n\io\managed\impl;

use n2n\io\managed\File;
use n2n\io\managed\FileLocator;

/**
 * FileLocator that organizes files into month-based folders (e.g., 202504/).
 * 
 * The date is captured at the time of file persist (upload), creating a directory structure
 * with up to 12 folders per year. This locator only affects storage location - file retrieval
 * uses the qualified name stored in the database.
 */
class MonthlyDirFileLocator implements FileLocator {

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
		return [...$this->prefixLevels, date('Ym')];
	}

	function buildFileName(File $file): ?string {
		if (!$this->uniqueSuffix) {
			return null;
		}

		return DailyDirFileLocator::createTokenFileName($file->getOriginalName());
	}
}
