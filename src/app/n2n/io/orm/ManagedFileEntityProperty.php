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
namespace n2n\io\orm;

use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\persistence\orm\query\from\MetaTreePoint;
use n2n\persistence\orm\query\QueryState;
use n2n\io\managed\FileLocator;
use n2n\persistence\orm\store\operation\MergeOperation;
use n2n\persistence\orm\EntityManager;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\property\impl\ColumnPropertyAdapter;
use n2n\persistence\orm\store\action\PersistAction;
use n2n\io\managed\FileManager;
use n2n\persistence\orm\store\action\RemoveAction;
use n2n\reflection\ArgUtils;
use n2n\io\managed\File;
use n2n\persistence\orm\property\ColumnComparableEntityProperty;
use n2n\persistence\orm\criteria\compare\ManagedFileColumnComparable;

class ManagedFileEntityProperty extends ColumnPropertyAdapter implements ColumnComparableEntityProperty {
	private $fileManagerClassName;
	private $fileLocator;
	private $cascadeDelete;
	
	public function __construct(AccessProxy $accessProxy, $columnName, $fileManagerClassName, bool $cascadeDelete) {
		$accessProxy->setConstraint(TypeConstraint::createSimple('n2n\io\managed\File', true));
	
		parent::__construct($accessProxy, $columnName);
		$this->fileManagerClassName = $fileManagerClassName;
		$this->cascadeDelete = $cascadeDelete;
	}
	/**
	 * @return string 
	 */
	public function getFileManagerClassName() {
		return $this->fileManagerClassName;
	}
	/**
	 * @return FileLocator
	 */
	public function getFileLocator() {
		return $this->fileLocator;
	}
	/**
	 * @param FileLocator $fileLocator
	 */
	public function setFileLocator(FileLocator $fileLocator = null) {
		$this->fileLocator = $fileLocator;
	}
	/**
	 * @param EntityManager $em
	 * @throws IllegalStateException
	 * @return FileManager
	 */
	private function lookupFileManager(EntityManager $em) {
		if (null !== ($magicContext = $em->getMagicContext())) {
			$fileManager = $magicContext->lookup($this->fileManagerClassName);
			IllegalStateException::assertTrue($fileManager instanceof FileManager);
			return $fileManager;
		}
		
		throw new IllegalStateException('File property can not be used outside of a MagicContext.');
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::createSelection()
	 */
	public function createSelection(MetaTreePoint $metaTreePoint, QueryState $queryState) {
		return new ManagedFileSelection($this->createQueryColumn($metaTreePoint->getMeta()), 
				$this->lookupFileManager($queryState->getEntityManager()), $this);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\ColumnComparableEntityProperty::createComparisonStrategy()
	 */
	public function createColumnComparable(MetaTreePoint $metaTreePoint, QueryState $queryState) {
		return new ManagedFileColumnComparable($this->createQueryColumn($metaTreePoint->getMeta()), $queryState,
				$this->lookupFileManager($queryState->getEntityManager()));
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::mergeValue()
	 */
	public function mergeValue($value, $sameEntity, MergeOperation $mergeOperation) {
		return $value;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::supplyPersistAction()
	 */
	public function supplyPersistAction($value, $valueHash, PersistAction $persistingJob) {
		$fileManager = $this->lookupFileManager($persistingJob->getActionQueue()->getEntityManager());
		
		$oldQualifiedName = null;
		if ($valueHash !== null && 2 == count($parts = explode(self::FM_FILE_VH_SEPERATOR, $valueHash, 2))) {
			$oldQualifiedName = $parts[1];
		}
		
		if ($value === null) {
			if ($this->cascadeDelete && $oldQualifiedName !== null) {
				$fileManager->removeByQualifiedName($oldQualifiedName);
			}
			
			$persistingJob->getMeta()->setRawValue($this->getEntityModel(), $this->columnName, null);
			return;
		}
		
		$qualifiedName = $fileManager->persist($value, $this->fileLocator);
		if ($this->cascadeDelete && $oldQualifiedName !== null && $oldQualifiedName !== $qualifiedName) {
			$fileManager->removeByQualifiedName($oldQualifiedName);
		}
		
		$persistingJob->getMeta()->setRawValue($this->getEntityModel(), $this->columnName, $qualifiedName);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::supplyRemoveAction()
	 */
	public function supplyRemoveAction($value, $valueHash, RemoveAction $removeAction) {
		if ($value === null) return;
		ArgUtils::assertTrue($value instanceof File);
		
		if ($this->cascadeDelete && $value->isValid()) {
			$this->lookupFileManager($removeAction->getActionQueue()->getEntityManager())->remove($value);
		}
	}
	const FM_FILE_VH_SEPERATOR = ':';
	
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::buildValueHash()
	 */
	public function buildValueHash($value, EntityManager $em) {
		if ($value === null) return null;
		ArgUtils::assertTrue($value instanceof File);
		
		$qualifiedName = null;
		if ($value instanceof UnknownFile) {
			$qualifiedName = $value->getQualifiedName();
		} else {
			$qualifiedName = $this->lookupFileManager($em)->checkFile($value);
		}
		
		return $this->fileManagerClassName . self::FM_FILE_VH_SEPERATOR . $qualifiedName;
	}
}
