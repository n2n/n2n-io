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

use n2n\util\HashUtils;
use n2n\reflection\ArgUtils;
use n2n\io\fs\FsPath;
use n2n\core\Sync;
use n2n\io\IoException;
use n2n\util\uri\Url;
use n2n\io\managed\File;
use n2n\io\managed\FileLocator;
use n2n\io\managed\FileManagingConstraintException;
use n2n\io\managed\InvalidFileSourceException;
use n2n\io\managed\impl\CommonFile;
use n2n\util\ex\IllegalStateException;

class TransactionFileEngine {
	const GENERATED_LEVEL_LENGTH = 6;
	
	const FILE_SUFFIX = '.managed';
	const FILEINFO_SUFFIX = '.inf';
	
	private $fileManagerName;
	private $baseDirFsPath;
	private $dirPerm;
	private $filePerm;
	
	private $baseUrl;
	private $customFileNamesAllowed = false;
	
	private $filePersistJobs = array();
	private $fileRemoveJobs = array();
	
	public function __construct($fileManagerName, FsPath $baseDirFsPath, $dirPerm, $filePerm) {
		$this->fileManagerName = $fileManagerName;
		$this->baseDirFsPath = $baseDirFsPath;
		$this->dirPerm = $dirPerm;
		$this->filePerm = $filePerm;
	}
	
	public function setCustomFileNamesAllowed($customFileNamesAllowed) {
		$this->customFileNamesAllowed = $customFileNamesAllowed;
	}
	
	public function isCustomFileNamesAllowed() {
		return $this->customFileNamesAllowed;
	}
	
	public function setBaseUrl(Url $baseUrl) {
		$this->baseUrl = $baseUrl;
	}
	/**
	 * @return Url 
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}
	/**
	 * @param string $fileName
	 * @param FileLocator $fileLocator
	 * @return \n2n\io\managed\FileAssignation
	 * @throws FileManagingConstraintException
	 */
	public function persist(File $file, FileLocator $fileLocator = null) {
		if ($this->containsFile($file)) {
			return $file->getFileSource()->getQualifiedName();
		}
		
		$dirLevelNames = array();
		if ($fileLocator !== null) {
			$dirLevelNames = $fileLocator->buildDirLevelNames($file);
			ArgUtils::valArrayReturn($dirLevelNames, $fileLocator, 'buildDirLevels', 'scalar');
		}

		$fileName = $this->determineFileName($file, $fileLocator);
		try {
			return $this->createFilePersistJob($dirLevelNames, $fileName, $file);
		} catch (QualifiedNameFormatException $e) {
			throw new FileManagingConstraintException('FileLocator provides invalid level names', 0, $e);
		}
	}
	
	private function determineFileName(File $file, FileLocator $fileLocator = null) {
		if (!$this->customFileNamesAllowed) {
			return $this->generateFileName() . self::FILE_SUFFIX;
		}

		if ($fileLocator !== null) {
			$fileName = $fileLocator->buildFileName($file);
			ArgUtils::valTypeReturn($fileName, 'scalar', $fileLocator, 'buildFileName', true);
			if ($fileName !== null) return $fileName;
		}	
		
		try{
			return QualifiedNameBuilder::qualifyFileName($file->getOriginalName());
		} catch (\InvalidArgumentException $e) {
			return $this->generateFileName();
		}
	}
		
	private function generateFileName() {
		return HashUtils::base36Md5Hash(uniqid(), self::GENERATED_LEVEL_LENGTH);
	}

	private function createFilePersistJob($dirLevelNames, $fileName, File $file) {
		if (!$file->getFileSource()->isValid()) {
			throw new InvalidFileSourceException('FileSource of File no longer valid: ' . $file);
		}
		
		$qnb = new QualifiedNameBuilder($dirLevelNames, $fileName);
		$dirPath = $this->baseDirFsPath->ext($dirLevelNames);
		$dirPath->mkdirs($this->dirPerm);
		$this->ensureWritable($dirPath);
		
		$filePath = $dirPath->ext($fileName);
		
		$ext = 2;
		$usedFileName = $fileName;
		$lock = null;

		while ($filePath->exists() || null === ($lock = Sync::exNb($this, (string) $filePath))) {
			$fileNameParts = explode('.', $fileName);
			$fileNameParts[0] .= $ext++;
			$usedFileName = implode('.', $fileNameParts);
			$qnb = new QualifiedNameBuilder($dirLevelNames, $usedFileName);
			$filePath = $dirPath->ext($usedFileName);
		}
		
		$qualifiedName = $qnb->__toString();
		$managedFileSource = new ManagedFileSource($filePath, $this->fileManagerName, $qualifiedName, $this->dirPerm, $this->filePerm);
		if ($this->baseUrl !== null) {
			$managedFileSource->setUrl($this->baseUrl->pathExt($qnb->toArray()));
		}
		
		$this->filePersistJobs[$qualifiedName] = new FilePersistJob($file, $managedFileSource, $lock);
		return $qualifiedName;
	}
	
	private function ensureWritable(FsPath $fsPath) {
		if ($fsPath->isWritable()) return;
		
		if ($fsPath->isDir()) {
			throw new IoException('Directory is not writable: ' . $fsPath->__toString());
		}
		
		if ($fsPath->isFile()) {
			throw new IoException('File is not writable: ' . $fsPath->__toString());
		}
		
		throw new \InvalidArgumentException();
	}


	/**
	 * @param string $qualifiedName
	 * @return File
	 */
	public function getByQualifiedName(string $qualifiedName) {
		if (isset($this->filePersistJobs[$qualifiedName])) {
			return $this->filePersistJobs[$qualifiedName]->getFile();
		}
		
		$qnBuilder = QualifiedNameBuilder::createFromString($qualifiedName);

		$fileFsPath = $this->baseDirFsPath->ext($qnBuilder->toArray());
		
		if (!$fileFsPath->isFile()) {
			return null;
		}
		
		$managedFileSource = new ManagedFileSource($fileFsPath, $this->fileManagerName, $qualifiedName, 
				$this->dirPerm, $this->filePerm);
		
		if ($this->baseUrl !== null) {
			$managedFileSource->setUrl($this->baseUrl->pathExt($qnBuilder->toArray()));
		}
		
		return new CommonFile($managedFileSource, $fileFsPath->getName());
	}
	
	public function containsFile(File $file) {
		return $file->isValid() && $file->getFileSource() instanceof ManagedFileSource 
				&& $file->getFileSource()->getFileManagerName() == $this->fileManagerName;
	}
	
	public function checkFile(File $file) {
		$fileSource = $file->getFileSource();
		if ($fileSource instanceof ManagedFileSource && $fileSource->isValid() 
				&& $fileSource->getFileManagerName() == $this->fileManagerName) {
			return $fileSource->getQualifiedName();
		}
		
		return null;
	}
	
	public function remove(File $file) {
		if (!$this->containsFile($file)) return;
		
		$managedFileSource = $file->getFileSource();
		IllegalStateException::assertTrue($managedFileSource instanceof ManagedFileSource);
		$qualifiedName = $managedFileSource->getQualifiedName();
		
		if (isset($this->fileRemoveJobs[$qualifiedName])) {
			return $this->fileRemoveJobs[$qualifiedName];
		}
		
		if (isset($this->filePersistJobs[$qualifiedName])) {
			unset($this->filePersistJobs[$qualifiedName]);
			return;
		}
		
		
		$this->ensureWritable($managedFileSource->getFileFsPath());
		return $this->fileRemoveJobs[$qualifiedName] = new FileRemoveJob($managedFileSource);
		
	}
	
	public function removeByQualifiedName($qualifiedName) {
		if (null !== ($file = $this->getByQualifiedName($qualifiedName))) {
			$this->remove($file);
		}
		
		return;
	}
	
	private $persistedFiles = array();
	
	public function flush(bool $persistOnly = false) {
		while (null !== ($filePersistJob = array_pop($this->filePersistJobs))) {
			$filePersistJob->execute();
			$this->persistedFiles[] = $filePersistJob->getFile();
		}
		
		if ($persistOnly) return;
		
		while (null !== ($fileRemoveJob = array_pop($this->fileRemoveJobs))) {
			$fileRemoveJob->execute();
		}
		
		$this->clearBuffer();
	}
	
	public function abortFlush() {
		$this->filePersistJobs = array();
		$this->fileRemoveJobs = array();
		
		while (null !== ($persistedFile = array_pop($this->persistedFiles))) {
			$this->remove($persistedFile);
		}

		$this->flush();
	}
	
	public function clearBuffer() {
		$this->persistedFiles = array();
		$this->filePersistJobs = array();
		$this->fileRemoveJobs = array();
	}
}


// namespace n2n\io\fs;

// use n2n\io\managed\FileLocator;
// use n2n\io\fs\FsPath;
// use n2n\util\HashUtils;
// use n2n\util\StringUtils;
// use n2n\core\SysTextUtils;
// use n2n\io\IoUtils;

// class FileAssignationFactory {

// 	private $fileManager;
// 	/**
// 	 *
// 	 * @param string $allowLevels
// 	 * @param array $reservedPrefixes
// 	*/
// 	public function __construct(FileManager $fileManager, FsPath $baseDirPath, $dirPerm, $filePerm, array $reservedPrefixes = array()) {
// 		$this->fileManager = $fileManager;
// 		$this->baseDirPath = $baseDirPath;
// 		$this->dirPerm = $dirPerm;
// 		$this->filePerm = $filePerm;
// 		$this->reservedPrefixes = $reservedPrefixes;
// 	}

// 	public function createTmp($prefix, $suffix = null) {
// 		if (!isset($suffix)) {
// 			$filePath = new FsPath(tempnam($this->baseDirPath, $prefix));
// 			$filePath->chmod($this->filePerm);
// 			return new FileAssignation($this->fileManager, $filePath, $filePath->getName());
// 		}

// 		$tmpPath = new FsPath(tempnam($this->baseDirPath, $prefix));
// 		$tmpPath->delete();

// 		$fileName = $tmpPath->getFileName() . $suffix . '.' . $tmpPath->getExtension();
// 		return $this->createFilePath(array(), $this->qualifyFileName($fileName));
// 	}
