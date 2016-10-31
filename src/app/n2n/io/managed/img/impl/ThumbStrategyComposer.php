<?php
namespace lib\n2n\io\managed\img\impl;

use n2n\io\managed\img\ImageFile;
use n2n\reflection\ArgUtils;

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
	
	public function createUiThumbStrategy(): UiThumbStrategy {
		$widths = $this->fixedWidths;
		ksort($widths, SORT_NUMERIC);
		
		$width = reset($widths);
		
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
	private $width;
	private $height;
	
	public function __construct(int $width, int $height) {
		$this->width = $width;
	}
	
	public function getImageFile() {
		
	}
	
	public function getSize() {
	
	}
	
}