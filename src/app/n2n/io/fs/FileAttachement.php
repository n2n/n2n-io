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
namespace n2n\io\fs;

use n2n\web\http\ResourceResponseObject;
use n2n\web\http\Response;
use n2n\io\IoUtils;
use n2n\util\ex\NotYetImplementedException;
use n2n\io\managed\File;

class FileAttachement extends ResourceResponseObject {
	private $file;
	private $name;
	
	public function __construct(File $file, $name = null) {
		$this->file = $file;
		$this->name = $name;	
	}
	/* (non-PHPdoc)
	 * @see \n2n\web\http\ResourceResponseObject::responseOut()
	 */
	public function responseOut() {
		$this->file->responseOut();
	}
	/* (non-PHPdoc)
	 * @see \n2n\web\http\ResourceResponseObject::getEtag()
	 */
	public function getEtag() {
		return $this->file->getEtag();
	}
	/* (non-PHPdoc)
	 * @see \n2n\web\http\ResourceResponseObject::getLastModified()
	 */
	public function getLastModified() {
		return $this->file->getLastModified();	
	}
	/* (non-PHPdoc)
	 * @see \n2n\web\http\ResponseObject::prepareForResponse()
	 */
	public function prepareForResponse(Response $response) {
		$this->file->prepareForResponse($response);
			
		$name = $this->name !== null ? $this->name : $this->file->getOriginalName();
		if (IoUtils::hasSpecialChars($name)) {
			throw new NotYetImplementedException('RFC-2231 encoding not yet implemented');
		}
			
		$response->setHeader('Content-Disposition: attachment;filename="' . $name . '"');
	}
	/* (non-PHPdoc)
	 * @see \n2n\web\http\ResponseObject::toKownResponseString()
	 */
	public function toKownResponseString(): string {
		return $this->file->toResponseString();
	}
}
