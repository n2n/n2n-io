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

use n2n\io\fs\FsPath;
use n2n\util\JsonEncodeFailedException;
use n2n\io\IoException;
use n2n\util\JsonDecodeFailedException;
use n2n\util\StringUtils;
use n2n\io\IoUtils;
use n2n\io\managed\FileManagingException;

class FileInfoDingsler {
	const INFO_SUFFIX = '.inf';

	private $infoFsPath;

	public function __construct(FsPath $fsPath) {
		$this->infoFsPath = new FsPath($fsPath->__toString() . self::INFO_SUFFIX);
	}

	public function getInfoFsPath() {
		return $this->infoFsPath;
	}

	public function exists() {
		return $this->infoFsPath->exists();
	}

	public function write(array $data) {
		try {
			IoUtils::putContents($this->infoFsPath, StringUtils::jsonEncode($data));
		} catch (JsonEncodeFailedException $e) {
			throw $this->createWriteException($e);
		} catch (IoException $e) {
			throw $this->createWriteException($e);
		}
	}

	public function read() {
		try{
			return StringUtils::jsonDecode(IoUtils::getContents($this->infoFsPath), true);
		} catch (JsonDecodeFailedException $e) {
			throw $this->createReadException($e);
		} catch (IoException $e) {
			throw $this->createReadException($e);
		}
	}

	private function createWriteException($e) {
		throw new FileManagingException('Could no write info file: ' . $this->infoFsPath, 0, $e);
	}

	private function createReadException($e) {
		throw new FileManagingException('Could no read from info file: ' . $this->infoFsPath, 0, $e);
	}
}
