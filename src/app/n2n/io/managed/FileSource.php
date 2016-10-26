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
namespace n2n\io\managed;

use n2n\util\uri\Url;
use n2n\io\fs\FsPath;
use n2n\io\InputStream;
use n2n\io\img\ImageSource;

interface FileSource {
	/**
	 * @return InputStream
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed {@link FileSource::isValid()}.
	 */
	public function createInputStream(): InputStream;
	
	/**
	 * @return int
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed {@link FileSource::isValid()}.
	 */
	public function getSize(): int;
	
	/**
	 * @return \DateTime|null null if not known 
	 */
	public function getLastModified();
	
	/**
	 * @return string 
	 */
	public function buildHash(): string;

	/**
	 * @return boolean false if {@link FileSource} is the FileSource is no longer accessible.
	 */
	public function isValid(): bool;
	
	/**
	 * @return boolean
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}).
	 */
	public function isHttpaccessible(): bool;
	
	/**
	 * @param Url $url
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}).
	 */
	public function setUrl(Url $url);
	
	/**
	 * @return Url
	 * @throws InaccessibleFileSourceException if {@link FileSource} is disposed ({@link self::isValid()}).
	 */
	public function getUrl(): Url;
	
	/**
	 * @return boolean
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}).
	 */
	public function isImage(): bool;
	
	/**
	 * @return \n2n\io\img\ImageSource
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}).
	 * @throws \n2n\io\img\UnsupportedImageTypeException if {@link self::isImage()} returns false.
	 */
	public function createImageSource(): ImageSource;
	
	/**
	 * @return boolean
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}.
	 */
	public function isThumbSupportAvailable(): bool;
	
	/**
	 * @return FileSourceThumbEngine
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}.
	 * @throws \n2n\io\img\UnsupportedImageTypeException
	 * @throws NoThumbSupportException
	 */
	public function getFileSourceThumbEngine(): FileSourceThumbEngine;
	
	/**
	 * @param FsPath $fsPath
	 * @param string $filePerm
	 * @param bool $overwrite
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed {@link FileSource::isValid()}.
	 * @throws IoException
	 * @throws FileManagingConstraintException if file is not allowed to be copied.
	 */
	public function move(FsPath $fsPath, $filePerm, $overwrite = false);
	
	/**
	 * @param FsPath $fsPath
	 * @param string $filePerm
	 * @param string $overwrite
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link FileSource::isValid()}).
	 * @throws IoException
	 * @throws FileManagingConstraintException if file is not allowed to be copied.
	 */
	public function copy(FsPath $fsPath, $filePerm, $overwrite = false);
	
	/**
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}).
	 * @throws IoException
	 * @throws FileManagingConstraintException if file is not allowed to be deleted.
	 */
	public function delete();
	
	/**
	 * @return string
	 */
	public function __toString(): string;
}