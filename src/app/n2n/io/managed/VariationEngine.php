<?php
namespace n2n\io\managed;

interface VariationEngine {
	
	public function getThumbManager(): ThumbManager;
	
	public function getVariationManager(): VariationManager;
}