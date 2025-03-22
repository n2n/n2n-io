<?php

namespace n2n\io\test;

use n2n\io\managed\img\ImageMimeType;
use n2n\io\img\ImageSource;
use n2n\io\img\impl\ImageSourceFactory;
use n2n\util\io\fs\FsPath;

class IoTestEnv {

	static function createImageFsPath(string $fileName): FsPath {
		return new FsPath(__DIR__ . DIRECTORY_SEPARATOR . 'testimages' . DIRECTORY_SEPARATOR . $fileName);
	}

	static function createImageSource(string $fileName, ImageMimeType $imageMimeType): ImageSource {
		return ImageSourceFactory::createFromFileName((string) self::createImageFsPath($fileName),
				$imageMimeType->value);
	}
}