<?php

namespace n2n\io\managed\impl;

use n2n\io\managed\File;
use n2n\io\managed\FileLocator;

/**
 * FileLocator that organizes files into date-based folders (e.g., 2025-04-01/).
 * 
 * The date is captured at the time of file persist (upload), creating a directory structure
 * with up to 365 folders per year. This locator only affects storage location - file retrieval
 * uses the qualified name stored in the database.
 */
class DailyDirFileLocator implements FileLocator {

	private array $prefixLevels;

	// Info.pdf -> Info-18as923.pdf
	// Info.holeradio.pdf -> Info-18as923.holeradiopdf
	// use TokenUtils::randomToken()

	/**
	 * @param string ...$prefixLevels Optional prefix directories before the date folder (e.g., 'imports', 'exports')
	 */
	function __construct(bool $uniqueSuffix = true, string ...$prefixLevels) {
		$this->prefixLevels = $prefixLevels;
	}

	function buildDirLevelNames(File $file): array {
		return [...$this->prefixLevels, date('Ymd')];
	}

	function buildFileName(File $file): ?string {
		return null;
	}
}
