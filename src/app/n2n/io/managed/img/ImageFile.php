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
 * Bert Hofmänner.......: Idea, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\io\managed\img;

use n2n\io\managed\File;
use n2n\io\img\ImageResource;
use n2n\io\managed\impl\CommonFile;

class ImageFile {	
	private $file;
	private $imageSource;
	/**
	 * @param File $file 
	 */
	public function __construct(File $file) {
		$this->file = $file;
		$this->imageSource = $this->file->getFileSource()->createImageSource();
	}
	/**
	 * 
	 * @return File
	 */
	public function getFile() {
		return $this->file;
	}
	/**
	 * @return \n2n\io\img\ImageSource
	 */
	public function getImageSource() {
		return $this->imageSource;
	}
	
	public function getWidth() {
		return $this->imageSource->getWidth();
	}
	
	public function getHeight() {
		return $this->imageSource->getHeight();
	}
	
	public function crop($x, $y, $width, $height) {
		$imageSource = $this->createImageSource();
		$imageResource = $imageSource->createResource();
		$imageResource->crop($x, $y, $width, $height);
		$imageSource->saveImageResource($imageResource);
		$imageResource->destroy();
	}

	public function resize($width, $height){
		$imageSource = $this->createImageSource();
		$imageResource = $imageSource->createResource();
		$imageResource->resize($width, $height);
		$this->saveImageResource($imageResource);
		$imageResource->destroy();
	}
	
	public function proportionalResize($width, $height, $cropAllowed = false) {
		$imageSource = $this->createImageSource();
		$imageResource = $imageSource->createResource();
		$imageResource->proportionalResize($width, $height, $cropAllowed);
		$this->saveImageResource($imageResource);
		$imageResource->destroy();
	}

	public function watermark(ImageResource $watermark, $watermarkPos = 4, $watermarkMargin = 10) {
		$imageSource = $this->createImageSource();
		$imageResource = $imageSource->createResource();
		$imageResource->watermark($watermark, $watermarkPos, $watermarkMargin);
		$this->saveImageResource($imageResource);
		$imageResource->destroy();
	}
	
	public function getThumbFile(ImageDimension $imageDimension) {
		$thumbEngine = $this->file->getFileSource()->getThumbManager();
		
		if (null !== ($thumbFileResource = $thumbEngine->getByDimension($imageDimension))) {
			return new CommonFile($thumbFileResource, $this->file->getOriginalName());
		}
		
		return null;
	}
		
	public function createThumbFile(ImageDimension $imageDimension, ImageResource $imageResource): File {
		return new CommonFile($this->file->getFileSource()->getThumbManager()
						->create($imageResource, $imageDimension),
				$this->file->getOriginalName());
	}
	
	/**
	 * @return ImageFile
	 */
	public function getOrCreateThumb(ThumbStrategy $thumbStrategy): ImageFile {
		$thumbEngine = $this->file->getFileSource()->getVariationEngine()->getThumbManager();
		$imageDimension = $thumbStrategy->getImageDimension();
		
		$thumbFileResource = $thumbEngine->getByDimension($imageDimension);
		if ($thumbFileResource !== null) {
			return new ImageFile(new CommonFile($thumbFileResource, $this->file->getOriginalName()));
		}
		
		if ($thumbStrategy->matches($this->imageSource)) {
			return $this;
		}
		
		$imageResource = $this->imageSource->createImageResource();
		$thumbStrategy->resize($imageResource);
		
		$thumbFileResource = $thumbEngine->create($imageResource, $imageDimension);
		$imageResource->destroy();
		
		return new ImageFile(new CommonFile($thumbFileResource, $this->file->getOriginalName()));
	}
	
	public function getOrCreateVariation(ThumbStrategy $thumbStrategy): ImageFile {
		$variationManager = $this->file->getFileSource()->getVariationEngine()->getVariationManager();
		$imageDimension = $thumbStrategy->getImageDimension();
	
		$variationFileResource = $variationManager->getByKey($imageDimension);
		if ($variationFileResource !== null) {
			return new ImageFile(new CommonFile($variationFileResource, $this->file->getOriginalName()));
		}
	
		if ($thumbStrategy->matches($this->imageSource)) {
			return $this;
		}
	
		$imageResource = $this->imageSource->createImageResource();
		$thumbStrategy->resize($imageResource);
	
		$variationFileResource = $variationManager->createImage($imageDimension, $imageResource);
		$imageResource->destroy();
	
		return new ImageFile(new CommonFile($variationFileResource, $this->file->getOriginalName()));
	}
}
