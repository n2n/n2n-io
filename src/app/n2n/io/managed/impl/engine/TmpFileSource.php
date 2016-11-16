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
namespace n2n\io\managed\impl\engine;

use n2n\io\fs\FsPath;
use n2n\util\ex\UnsupportedOperationException;
use n2n\io\managed\ThumbManager;
use n2n\io\managed\VariationEngine;
use n2n\io\managed\VariationManager;

class TmpFileSource extends FileSourceAdapter {
	private $sessionId;
	
	public function __construct($qualifiedName, FsPath $fileFsPath, FsPath $infoFsPath = null, $sessionId = null) {
		parent::__construct($qualifiedName, $fileFsPath, $infoFsPath);
		$this->sessionId = $sessionId;
	}
		
	/**
	 * @return string
	 */
	public function getSessionId() {
		return $this->sessionId;
	}
	
	public function getVariationEngine(): VariationEngine {
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::isThumbSupportAvailable()
	 */
	public function hasThumbSupport(): bool {
		return false;
	}

	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::getThumbManager()
	 */
	public function getThumbManager(): ThumbManager {
		throw new UnsupportedOperationException('Thumb support not available for tmp file: ' . $this->fileFsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::isThumbSupportAvailable()
	 */
	public function hasVariationSupport(): bool {
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::getThumbManager()
	 */
	public function getVariationManager(): VariationManager {
		throw new UnsupportedOperationException('Variation support not available for tmp file: ' . $this->fileFsPath);
	}

	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return 'tmp file ' . $this->fileFsPath;
	}
	
	public function __destruct() {
		if ($this->sessionId === null && $this->isValid()) {
			$this->delete();
		}
	}
}
