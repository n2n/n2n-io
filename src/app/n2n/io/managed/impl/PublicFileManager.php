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
namespace n2n\io\managed\impl;

use n2n\core\config\FilesConfig;
use n2n\core\config\IoConfig;
use n2n\web\http\Request;
use n2n\io\managed\FileManager;
use n2n\context\RequestScoped;
use n2n\io\managed\impl\engine\transactional\TransactionalFileEngine;
use n2n\core\container\Transaction;

class PublicFileManager extends TransactionalFileManagerAdapter implements RequestScoped {
	
	private function _init(FilesConfig $filesConfig, IoConfig $ioConfig, ?Request $request = null) {
		$this->fileEngine = new TransactionalFileEngine(FileManager::TYPE_PUBLIC, $filesConfig->getManagerPublicDir(), 
				$ioConfig->getPublicDirPermission(), $ioConfig->getPublicFilePermission());
		$this->fileEngine->setCustomFileNamesAllowed(true);

		$url = $filesConfig->getManagerPublicUrl();
		if (!$url->isRelative() || $url->getPath()->hasLeadingDelimiter()) {
			$this->fileEngine->setBaseUrl($url);
		} else if ($request !== null) {
			$this->fileEngine->setBaseUrl($request->getContextPath()->ext($url->getPath())->toUrl());
		} else {
			$this->fileEngine->setBaseUrl($url);
		}
	}
}
