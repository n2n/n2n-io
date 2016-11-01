<?php
namespace lib\n2n\io\managed\img\impl;

use n2n\io\managed\img\ImageFile;
use n2n\reflection\ArgUtils;
use n2n\core\container\N2nContext;
use n2n\io\managed\img\impl\ProportionalThumbStrategy;
use n2n\io\img\ImageResource;

interface UiThumbStrategyComposer {
	
	/**
	 * @return ImageFile
	 */
	public function createDefaultImageFile(): ImageFile;
	
	/**
	 * @return ImageSourceSet[]
	 */
	public function createUiThumbStrategy(): array;
}

class UiThumbStrategy {
	private $defaultImageFile;
	private $imageSourceSets;
	
	public function __construct(ImageFile $defaultImageFile, array $imageSourceSets) {
		ArgUtils::valArray($imageSourceSets, ImageSourceSet::class);
		$this->defaultImageFile = $defaultImageFile;
		$this->imageSourceSets = $imageSourceSets;
	}
	
	public function getDefaultImageFile() {
		return $this->defaultImageFile;
	}
	
	public function setDefaultImageFile(ImageFile $defaultImageFile) {
		$this->defaultImageFile = $defaultImageFile;
	}
	
	public function getImageSourceSets() {
		return $this->imageSourceSets;
	}
	
	public function setImageSourceSets(array $imageSourceSets) {
		$this->imageSourceSets = $imageSourceSets;
	}
}

class ProportionalUiThumbStrategyComposer implements UiThumbStrategyComposer {
	private $width;
	private $height;
	private $autoCropMode;
	private $scaleUpAllowed;

	private $fixedWidths;
	private $maxWidth;
	private $minWidth;

	/**
	 * @param int $width
	 * @param int $height
	 * @param string $autoCropMode
	 * @param bool $scaleUpAllowed
	 */
	public function __construct(int $width, int $height, string $autoCropMode = null, bool $scaleUpAllowed = true) {
		$this->maxWidth = $this->minWidth = $this->width = $width;
		$this->height = $height;
		$this->autoCropMode = $autoCropMode;
		$this->scaleUpAllowed = $scaleUpAllowed;
	}

	/**
	 * @param int $width
	 * @return ProportiaonalThumbStrategyComposer
	 */
	public function toWidth(int $width) {
		if ($width > $this->maxWidth) {
			$this->maxWidth = $width;
			return $this;
		}
		
		if ($width < $this->minWidth) {
			$this->minWidth = $width;
			return $this;
		}
		
		return $this;
	}

	public function widths(int ...$widths) {
		foreach ($widths as $width) {
			$this->fixedWidths[$width] = $width;
		}
		return $this;
	}
	
	public function factors(float ...$factors) {
		foreach ($factors as $factor) {
			$width = (int) ceil($this->width * $factor);
			$this->fixedWidths[$width] = $width;
		}
		return $this;
	}
	
	
	
	public function createUiThumbStrategy(ImageFile $imageFile, N2nContext $n2nContext): UiThumbStrategy {
		$widths = $this->fixedWidths;
		$widths[$this->minWidth] = $this->minWidth;
		$widths[$this->width] = $this->width;
		$widths[$this->maxWidth] = $this->maxWidth;
		krsort($widths, SORT_NUMERIC);
		
		$thumbFile = null;
		$imageFiles = array();
		foreach ($widths as $width) {
			if ($thumbFile === null) {
				$imageFiles[$width] = $thumbFile = $this->createThumb($imageFile, $width);
				continue;
			}
			
			$imageFiles[$width] = $this->createVariation($thumbFile, $width);
		}
		
		$lastSize = null;
		$lastWidth = null;
		foreach ($imageFiles as $width => $imageFile) {
			if ($width > $this->maxWidth || $width < $this->minWidth) continue;
			
// 			$size = $imageFile->getFile()->getFileSource()->getSize();
// 			if (!$this->isSizeGabTooLarge($lastWidth, $lastWidth = $size)) continue;
			
// 			if ($lastSize > $size) {
				
// 			}
		}
		
		$files = array();
		
		$imageSources = new ImageSourceSet($width, $height)
		
		return $imageFiles;
	}
	
	const MIN_SIZE_GAB = 51200;
	
	private function isSizeGabTooLarge($largerSize, $size) {
		$diff = $largerSize - $size;
		if ($diff <= self::MIN_SIZE_GAB) return false;
		
		return ($largerSize / 3 < $diff);
	}
	
	private function calcGabWidth($largerWidth, $width) {
		$diff = $largerWidth - $width;
		
		if ($diff > $largerWidth * 0.75) {
			return $largerWidth - (int) ceil($diff / 2);
		}
		
		return null;
	}
	
	private function createStrategy($width) {
		$height = ceil($this->height / $this->width * $width);
		
		return new ProportionalThumbStrategy($width, $height, $this->autoCropMode, $this->scaleUpAllowed);
	}
	
	private function createThumb(ImageFile $imageFile, int $width) {
		return $imageFile->getOrCreateThumb($this->createStrategy($width));
	}
	
	private function createVariation(ImageFile $imageFile, int $width) {
		$strategy = $this->createStrategy($width);
		if ($strategy->matches($imageFile->getImageSource())) {
			return null;
		}
		
		return $imageFile->getOrCreateVariationFile($strategy);
	}
	
	
}

class Builder {
	
	public function __construct(ImageFile $imageFile, int $width, int $height) {
		
	}
	
}



class ProportionalUiThumbStrategy {
	private $width;
	private $height;
	private $autoCropMode;
	private $scaleUpAllowed;
	private $minWidth;
	
	public function __construct(int $width, int $height, string $autoCropMode = null, bool $scaleUpAllowed = true) {
		$this->width = $width;
		$this->height = $height;
		$this->autoCropMode = $autoCropMode;
		$this->scaleUpAllowed = $scaleUpAllowed;
	}

	public function getSizes() {
	
	}
	
	public function buildImageSourceSets() {
		
	}
}


class ImageSourceSet {
	private $files;
	private $sizeAttr;
	
	public function __construct(array $files, string $sizesAttr = null) {
		$this->files = $files;
		$this->sizeAttr = $sizesAttr;
	}
	
	public function getSizesAttr() {
		return $this->sizeAttr;
	}
	
	
}

class ImageSource {
	private $file;
	private $htmlLength;
	
	public function __construct(File $file, string $htmlLength) {
		$this->file = $file;
		$this->htmlLength = $htmlLength;
	}
}