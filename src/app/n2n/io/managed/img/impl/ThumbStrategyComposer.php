<?php
namespace lib\n2n\io\managed\img\impl;

interface ThumbStrategyComposer {

}

class ProportiaonalThumbStrategyComposer implements ThumbStrategyComposer {
	private $maxWidth;
	private $maxHeight;
	private $autoCropMode;
	private $scaleUpAllowed;

	private $minWidth;
	private $minHeight;

	/**
	 * @param int $width
	 * @param int $height
	 * @param string $autoCropMode
	 * @param bool $scaleUpAllowed
	 */
	public function __construct(int $width, int $height, string $autoCropMode = null, bool $scaleUpAllowed = true) {
		$this->maxWidth = $width;
		$this->maxHeight = $height;
		$this->autoCropMode = $autoCropMode;
		$this->scaleUpAllowed = $scaleUpAllowed;
	}

	/**
	 * @param int $width
	 * @return ProportiaonalThumbStrategyComposer
	 */
	public function toWidth(int $width) {
		if ($width > $this->maxWidth) {
			$this->maxHeight = $this->maxHeight * ($width / $this->maxWidth);
			$this->maxWidth = $width;
			return $this;
		}

		if ($this->maxWidth == $width || ($this->minWidth !== null && $this->minWidth <= $width)) {
			return $this;
		}
		
		return $this;
	}

	public function width(int $width) {
		
		return $this;
	}
	

	public function createThumbStrategy() {

	}
}


class ProportionalThumbStrategy {
	private $width;
	private $height;
	private $autoCropMode;
	private $scaleUpAllowed;
	
	public function __construct(int $width, int $height, string $autoCropMode = null, bool $scaleUpAllowed = true) {
		$this->width = $width;
		$this->height = $height;
		$this->autoCropMode = $autoCropMode;
		$this->scaleUpAllowed = $scaleUpAllowed;
	}
	
	public function getFileSources() {
		
	}
}