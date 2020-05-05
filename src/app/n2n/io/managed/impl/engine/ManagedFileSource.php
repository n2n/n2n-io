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
namespace n2n\io\managed\impl\engine;

use n2n\io\img\impl\ImageSourceFactory;
use n2n\io\fs\FsPath;
use n2n\io\managed\FileManagingConstraintException;
use n2n\io\managed\ThumbManager;
use n2n\io\managed\VariationEngine;
use n2n\io\managed\VariationManager;

class ManagedFileSource extends FileSourceAdapter implements VariationEngine {
	private $fileManagerName;
	private $dirPerm;
	private $filePerm;
	private $persistent = false;
	
	public function __construct(FsPath $fileFsPath, ?FsPath $infoFsPath, string $fileManagerName, string $qualifiedName, 
			string $dirPerm, string $filePerm) {
		parent::__construct($qualifiedName, $fileFsPath, $infoFsPath);
		$this->fileManagerName = $fileManagerName;
		$this->dirPerm = $dirPerm;
		$this->filePerm = $filePerm;
	}
	
	public function getDirPerm(): string {
		return $this->dirPerm;
	}
	
	public function getFilePerm(): string {
		return $this->filePerm;
	}
	
	public function getFileManagerName(): string {
		return $this->fileManagerName;
	}
	
	public function setPersisent($persistent) {
		$this->persistent = (boolean) $persistent;
	}
	
	public function isPersistent() {
		return $this->persistent;
	}
	
	public function move(FsPath $fsPath, $filePerm, $overwrite = false) {
		$this->ensureValid();
		
		throw new FileManagingConstraintException('File is managed by ' . $this->fileManagerName 
				. ' and can not be relocated: ' . $this->fileFsPath);
	}
	
	public function delete() {
		$this->ensureValid();
		
		throw new FileManagingConstraintException('File is managed by ' . $this->fileManagerName 
				. ' and can not be deleted: ' . $this->fileFsPath);
	}
	
	public function getVariationEngine(): VariationEngine {
		$this->ensureValid();
		
		return $this;
	}
	
	public function hasThumbSupport(): bool {
		return $this->isImage();
	}
	
	public function getThumbManager(): ThumbManager {
		$this->ensureValid();
		
		return new ManagedThumbManager($this, 
				ImageSourceFactory::getMimeTypeOfFile($this->fileFsPath), 
				$this->dirPerm, $this->filePerm);		
	}
	
	public function hasVariationSupport(): bool {
		return true;
	}
	
	public function getVariationManager(): VariationManager {
		$this->ensureValid();
	
		return new ManagedVariationManager($this,
				ImageSourceFactory::getMimeTypeOfFile($this->fileFsPath, false),
				$this->dirPerm, $this->filePerm);
	}
	
	public function clear() {
		if ($this->hasThumbSupport()) {
			$this->getThumbManager()->clear();
		}
		
		if ($this->hasVariationSupport()) {
			$this->getVariationManager()->clear();
		}
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return $this->fileFsPath . ' (managed by ' . $this->fileManagerName . ')';		
	}
}