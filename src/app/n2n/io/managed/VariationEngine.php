<?php
namespace n2n\io\managed;

interface VariationEngine {
	
	/**
	 * @return boolean
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link FileSource::isValid()}.
	 */
	public function hasThumbSupport(): bool;
	
	/**
	 * @return ThumbManager
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link FileSource::isValid()}.
	 * @throws \n2n\io\img\UnsupportedImageTypeException
	 */
	public function getThumbManager(): ThumbManager;
	
	/**
	 * @return boolean
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link FileSource::isValid()}.
	 */
	public function hasVariationSupport(): bool;
	
	/**
	 * @return ThumbManager
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link FileSource::isValid()}.
	 * @throws \n2n\io\img\UnsupportedImageTypeException
	 */
	public function getVariationManager(): VariationManager;
	
	public function clear();
}