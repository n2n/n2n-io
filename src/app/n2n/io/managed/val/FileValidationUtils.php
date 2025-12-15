<?php 
namespace n2n\io\managed\val;

use n2n\io\managed\File;
use n2n\util\col\ArrayUtils;

class FileValidationUtils {
	static function mimeTypeAllowed(File $file, array $allowedMimeTypes) {
		return ArrayUtils::inArrayLike($file->getFileSource()->getMimeType(),
				$allowedMimeTypes);
	}
	
	static function extensionAllowed(File $file, array $allowedExtensions) {
		return ArrayUtils::inArrayLike($file->getOriginalExtension(),
				$allowedExtensions);
	}
	static function sizeAllowed(File $file, int $maxSize): bool {
		return $file->getFileSource()->getSize() <= $maxSize;
	}
}