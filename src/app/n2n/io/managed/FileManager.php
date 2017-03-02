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

interface FileManager {
	const TYPE_PUBLIC = 'n2n\io\managed\impl\PublicFileManager';
	const TYPE_PRIVATE = 'n2n\io\managed\impl\PrivateFileManager';
	/**
	 * @param File $file
	 * @param FileLocator $fileLocator can be ignored by file manager
	 * @return string
	 * @throws FileManagingConstraintException if passed File or FileLocator violates any FileManager constraints.
	 * @throws FileManagingException on internal FileManager error 
	 */
	public function persist(File $file, FileLocator $fileLocator = null): string;
	
	/**
	 * @param File
	 * @return string qualified name or null if not managed by this FileManager.
	 * @throws FileManagingException
	 */
	public function checkFile(File $file);
	
	/**
	 * @param string $qualifiedName
	 * @return File or null if not found.
	 * @throws QualifiedNameFormatException if qualifiedName is invalid
	 * @throws FileManagingException 
	 */
	public function getByQualifiedName(string $qualifiedName = null);
	
	/**
	 * @param string $qualifiedName
	 * @param File $file
	 * @throws QualifiedNameFormatException if qualifiedName is invalid
	 * @throws FileManagingException
	 */
	public function removeByQualifiedName($qualifiedName);
	
	/**
	 * @param File $file
	 * @throws FileManagingConstraintException if passed File violates any FileManager constraints.
	 */
	public function remove(File $file);
	
	/**
	 * @throws FileManagingException on internal FileManager error 
	 */
	public function clear();
}
