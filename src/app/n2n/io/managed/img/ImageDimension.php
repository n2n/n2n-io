<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\io\managed\img;

class ImageDimension {
	const STR_ATTR_SEPARATOR = 'x';
	
	private $width;
	private $height;
	private $cropped;
	private $idExt;
	
	public function __construct(int $width, int $height, bool $cropped, string $idExt = null) {
		$this->width = $width;
		$this->height = $height;
		$this->idExt = $idExt;
	}
	
	public function getWidth(): int {
		return $this->width;
	}
	
	public function getHeight(): int {
		return $this->height;
	}
	
	public function isCropped(): bool {
		return $this->cropped;
	}
	
	public function getIdExt() {
		return $this->idExt;
	}
	
	public function __toString(): string {
		return $this->width . self::STR_ATTR_SEPARATOR . $this->height 
				. ($this->idExt !== null ? self::STR_ATTR_SEPARATOR . $this->idExt : '');
	}
	
	public static function createFromString($string): ImageDimension {
		$partParts = explode(self::STR_ATTR_SEPARATOR, trim($string), 3);
		if (2 > sizeof($partParts) || !is_numeric($partParts[0]) || !is_numeric($partParts[1])) {
			throw new \InvalidArgumentException('Dimension is invalid: ' . $string);
		}
		
		$width = (int) $partParts[0];
		$height = (int) $partParts[1];

		if ($width < 1 && $height < 1) {
			throw new \InvalidArgumentException();
		}
		
		$idExt = null;
		if (isset($partParts[2])) {
			$idExt = $partParts[2];
		}
			
		return new ImageDimension($width, $height, $idExt);
	}
}
