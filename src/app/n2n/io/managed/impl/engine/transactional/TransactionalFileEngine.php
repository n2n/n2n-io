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
namespace n2n\io\managed\impl\engine\transactional;

use n2n\util\HashUtils;
use n2n\util\type\ArgUtils;
use n2n\util\io\fs\FsPath;
use n2n\util\io\IoException;
use n2n\util\uri\Url;
use n2n\io\managed\File;
use n2n\io\managed\FileLocator;
use n2n\io\managed\FileManagingConstraintException;
use n2n\io\managed\impl\CommonFile;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\InaccessibleFileSourceException;
use n2n\io\managed\FileManagingException;
use n2n\io\managed\impl\engine\UncommittedManagedFileSource;
use n2n\io\managed\impl\engine\QualifiedNameBuilder;
use n2n\io\managed\impl\engine\variation\LazyFsAffiliationEngine;
use n2n\io\managed\impl\engine\QualifiedNameFormatException;
use n2n\io\managed\impl\engine\variation\FsThumbManager;
use n2n\concurrency\sync\Lock;
use n2n\concurrency\sync\impl\Sync;

class TransactionalFileEngine {
	const GENERATED_LEVEL_LENGTH = 6;

	const FILE_SUFFIX = '.managed';
	const FILEINFO_SUFFIX = '.inf';
	const INFO_ORIGINAL_NAME_KEY = 'originalName';
	const LOCK_FILE_EXT = '.lock';

	private $fileManagerName;
	private $baseDirFsPath;

	private $baseUrl;
	private $customFileNamesAllowed = false;

	private $filePersistJobs = array();
	private $fileRemoveJobs = array();

	public function __construct($fileManagerName, FsPath $baseDirFsPath,
			private string|int|null $dirPerm, private string|int|null $filePerm) {
		$this->fileManagerName = $fileManagerName;
		$this->baseDirFsPath = $baseDirFsPath;
	}

	public function setCustomFileNamesAllowed(bool $customFileNamesAllowed) {
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
	 * @return string
	 * @throws FileManagingConstraintException
	 */
	public function persist(File $file, ?FileLocator $fileLocator = null): ?string {
		if (null !== ($qn = $this->checkFile($file))) {
			return $qn;
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

	private function determineFileName(File $file, ?FileLocator $fileLocator = null) {
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


	private function acquireLock(FsPath $fileFsPath): ?Lock {
		$lock = Sync::byFileLock(new FsPath($fileFsPath . self::LOCK_FILE_EXT));

		if ($lock->acquireNb()) {
			return $lock;
		}

		return null;
	}

	private function createFilePersistJob($dirLevelNames, $fileName, File $file) {
		if (!$file->getFileSource()->isValid()) {
			throw new InaccessibleFileSourceException('FileSource of File no longer valid: ' . $file);
		}

		$qnb = new QualifiedNameBuilder($dirLevelNames, $fileName);
		$dirFsPath = $this->baseDirFsPath->ext($dirLevelNames);
		$dirFsPath->mkdirs($this->dirPerm);
		$this->ensureWritable($dirFsPath);

		$fileFsPath = $dirFsPath->ext($fileName);

		$ext = 2;
		$usedFileName = $fileName;
		$lock = null;

		// lock could succeed because of orphan lock removal
		while (isset($this->filePersistJobs[$qnb->__toString()])
				|| $fileFsPath->exists()
				|| null === ($lock = $this->acquireLock($fileFsPath))) {
			$fileNameParts = explode('.', $fileName);
			$fileNameParts[0] .= $ext++;
			$usedFileName = implode('.', $fileNameParts);
			$qnb = new QualifiedNameBuilder($dirLevelNames, $usedFileName);
			$fileFsPath = $dirFsPath->ext($usedFileName);
		}

// // 		$infoFsPath = null;
// // 		if (!$this->customFileNamesAllowed) {
// 			$fileInfoDingsler = new FileInfoDingsler($fileFsPath);
// 			$fileInfoDingsler->write(array(self::INFO_ORIGINAL_NAME_KEY => $file->getOriginalName()));
// 			$infoFsPath = $fileInfoDingsler->getInfoFsPath();
// // 		}

		$qualifiedName = $qnb->__toString();
		$managedFileSource = new ManagedFileSource($fileFsPath, $this->fileManagerName, $qualifiedName);
		$fileInfo = $file->getFileSource()->readFileInfo();
		$fileInfo->setOriginalName($file->getOriginalName());
		$managedFileSource->writeFileInfo($fileInfo);
		if ($this->baseUrl !== null) {
			$managedFileSource->setUrl($this->baseUrl->pathExt($qnb->toArray()));
		}

		$affiliationEngine = new LazyFsAffiliationEngine($managedFileSource, $this->dirPerm, $this->filePerm,
				$this->customFileNamesAllowed);
		// delete orphaned file
		$affiliationEngine->clear();
		$managedFileSource->setAffiliationEngine($affiliationEngine);

		$file->setFileSource(new UncommittedManagedFileSource($file->getFileSource(), $managedFileSource));
		$this->filePersistJobs[$qualifiedName] = new FilePersistJob($file, $managedFileSource, $lock, $this->filePerm);
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
	 * @throws FileManagingException
	 */
	public function getByQualifiedName(string $qualifiedName, bool $ifExistsChecked = true): ?File {
		if (isset($this->filePersistJobs[$qualifiedName])) {
			return $this->filePersistJobs[$qualifiedName]->getFile();
		}

		$qnBuilder = QualifiedNameBuilder::createFromString($qualifiedName);

		$fileFsPath = $this->baseDirFsPath->ext($qnBuilder->toArray());
		$fileExists = $fileFsPath->isFile();

		if ($ifExistsChecked && !$fileExists) {
			return null;
		}

		$managedFileSource = new ManagedFileSource($fileFsPath, $this->fileManagerName, $qualifiedName);

		if ($this->customFileNamesAllowed) {
			$originalName = $fileFsPath->getName();
		} else if (!$fileExists) {
			$originalName = 'unknown-file-name';
		} else {
			$originalName = function () use ($managedFileSource) {
				$fileInfo = $managedFileSource->readFileInfo();
				$originalName = $fileInfo->getOriginalName();

				if ($originalName !== null) {
					return $originalName;
				}

//				$managedFileSource->delete();
				throw new FileManagingException('FileInfo with existing original name could not be obtained for:'
						. $managedFileSource);
			};
		}

		if ($this->baseUrl !== null) {
			$managedFileSource->setUrl($this->baseUrl->pathExt($qnBuilder->toArray()));
		}

		$managedFileSource->setAffiliationEngine(new LazyFsAffiliationEngine($managedFileSource, $this->dirPerm,
				$this->filePerm, $this->customFileNamesAllowed));

		return new CommonFile($managedFileSource, $originalName);
	}

	public function containsFile(File $file) {
		return null !== $this->checkFile($file);
	}

	public function checkFile(File $file): ?string {
		$fileSource = $file->getFileSource();

		if (!$fileSource->isValid()) return null;

		if ($fileSource instanceof UncommittedManagedFileSource) {
			$fileSource = $fileSource->getNewManagedFileSource();
		}

		if ($fileSource instanceof ManagedFileSource && $fileSource->getFileManagerName() == $this->fileManagerName) {
			return $fileSource->getQualifiedName();
		}

		return null;
	}

	public function remove(File $file) {
		if (!$this->containsFile($file)) return null;

		$managedFileSource = $file->getFileSource();
		IllegalStateException::assertTrue($managedFileSource instanceof ManagedFileSource);
		$qualifiedName = $managedFileSource->getQualifiedName();

		if (isset($this->fileRemoveJobs[$qualifiedName])) {
			return $this->fileRemoveJobs[$qualifiedName];
		}

		if (isset($this->filePersistJobs[$qualifiedName])) {
			$this->filePersistJobs[$qualifiedName]->dispose();
			unset($this->filePersistJobs[$qualifiedName]);
			return null;
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

	/**
	 * @param File $file
	 * @param FileLocator $fileLocator
	 * @return \n2n\io\managed\img\ImageDimension[]
	 */
	function getPossibleImageDimensions(File $file, ?FileLocator $fileLocator = null) {
		$dirFsPath = $this->baseDirFsPath;
		if ($fileLocator !== null) {
			$dirFsPath = $this->baseDirFsPath->ext($fileLocator->buildDirLevelNames($file));
		}
		return FsThumbManager::determinePossibleImageDimensions($dirFsPath);
	}
}
