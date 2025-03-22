<?php

namespace n2n\io\managed\img;

use http\Exception\InvalidArgumentException;

enum ImageMimeType: string {

	case JPEG = 'image/jpeg';
	case PNG = 'image/png';
	case GIF = 'image/gif';
	case WEBP = 'image/webp';

	function getExtension(): string {
		return match($this) {
			self::JPEG => 'jpg',
			self::PNG => 'png',
			self::GIF => 'gif',
			self::WEBP => 'webp'
		};
	}

	function toCode(): int {
		return match($this) {
			self::JPEG => 1,
			self::PNG => 2,
			self::GIF => 3,
			self::WEBP => 4
		};
	}

	static function fromCode(int $code): self {
		return match($code) {
			1 => self::JPEG,
			2 => self::PNG,
			3 => self::GIF,
			4 => self::WEBP,
			default => throw new InvalidArgumentException('Invalid ImageMimeType code: ' . $code)
		};
	}

}