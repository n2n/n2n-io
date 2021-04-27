<?php 
namespace n2n\io\managed\val;

use n2n\l10n\Message;

class FileValidators {
	static function mimeType(array $allowedMimeTypes, $errorMessage = null) {
		return new FileMimeTypeValidator($allowedMimeTypes, Message::build($errorMessage));
	}
	
	static function extension(array $allowedExtensions, $errorMessage = null) {
		return new ExtensionValidator($allowedExtensions, Message::build($errorMessage));
	}
}