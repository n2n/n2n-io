<?php 
namespace n2n\io\managed\val;

use n2n\l10n\Message;

class FileValidators {
	static function mimeType(array $allowedMimeTypes, $errorMessage) {
		return new FileMimeTypeValidator($allowedMimeTypes, Message::build($errorMessage));
	}
	
	static function extension(array $allowedExtensions, $errorMessage) {
		return new ExtensionValidator($allowedExtensions, Message::build($errorMessage));
	}
}