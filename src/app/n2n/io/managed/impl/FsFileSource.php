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
namespace n2n\io\managed\impl;

use n2n\io\managed\FileSource;
use n2n\io\IoUtils;
use n2n\io\img\impl\ImageSourceFactory;
use n2n\util\ex\UnsupportedOperationException;
use n2n\io\managed\impl\engine\FileSourceAdapter;
use n2n\io\fs\FsPath;
use n2n\io\managed\ThumbManager;
use n2n\io\img\ImageSource;
use n2n\io\InputStream;
use n2n\io\managed\VariationEngine;
use n2n\io\managed\VariationManager;
use n2n\io\CouldNotAchieveFlockException;
use n2n\io\fs\FileResourceStream;

class FsFileSource extends FileSourceAdapter implements FileSource {
	protected $fsPath;
	
	public function __construct(FsPath $fsPath) {
		parent::__construct(null, $fsPath);
		$this->fsPath = $fsPath;
	}
	
	public function getFsPath(): FsPath {
		return $this->fsPath;
	}
	
	public function getSize(): int {
		return IoUtils::filesize($this->fsPath);
	}
	
	public function isValid(): bool {
		return $this->fsPath->isFile();
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::createInputStream()
	 */
	public function createInputStream(): InputStream {
		$this->ensureValid();
		try {
			return IoUtils::createSafeFileInputStream($this->fsPath);
		} catch (CouldNotAchieveFlockException $e) {
			return new FileResourceStream($this->fsPath, 'r');
		}
	}
	
	public function isImage(): bool {
		return ImageSourceFactory::isFileSupported($this->fsPath);
	}
	
	public function createImageSource(): ImageSource {
		return ImageSourceFactory::createFromFileName($this->fsPath, 
			   ImageSourceFactory::getMimeTypeOfFile($this->fsPath));
	}
	
	public function getVariationEngine(): VariationEngine {
		return $this;
	}
	
	public function isThumbSupportAvailable(): bool {
		return false;
	}
	
	public function getThumbManager(): ThumbManager {
		return new UnsupportedOperationException('No thumb support provided for file: ' . $this->fsPath);
	}
	
	public function isVariationSupportAvailable() {
		return false;
	}
	
	public function getVariationManager(): VariationManager {
		return new UnsupportedOperationException('No variation support provided for file: ' . $this->fsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return $this->fsPath->__toString();
	}
}
