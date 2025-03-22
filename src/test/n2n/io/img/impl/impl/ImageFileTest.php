<?php

namespace n2n\io\img\impl\impl;

use PHPUnit\Framework\TestCase;
use n2n\util\io\fs\FsPath;
use n2n\util\io\fs\FileOperationException;
use n2n\io\managed\img\ImageFile;
use n2n\io\test\IoTestEnv;
use n2n\io\managed\impl\FileFactory;
use n2n\io\managed\impl\PublicFileManager;
use n2n\io\managed\impl\engine\variation\LazyFsAffiliationEngine;
use n2n\io\managed\impl\engine\transactional\ManagedFileSource;
use n2n\io\managed\File;
use n2n\io\managed\impl\CommonFile;
use n2n\io\managed\img\impl\ThSt;
use n2n\io\managed\img\ImageMimeType;
use n2n\io\img\impl\ImageSourceFactory;

class ImageFileTest extends TestCase {

	private ?FsPath $tempDirFsPath = null;
	private File $file;

	/**
	 * @throws FileOperationException
	 */
	function setUp(): void {
		$this->tempDirFsPath = new FsPath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid());
		$this->tempDirFsPath->mkdirs();

		$fileFsPath = $this->tempDirFsPath->ext('12x12.png');
		IoTestEnv::createImageFsPath('12x12.png')->copy($fileFsPath);
		$fileSource = new ManagedFileSource($fileFsPath, 'holeradio', 'qualified/holeradio');
		$fileSource->setAffiliationEngine(new LazyFsAffiliationEngine($fileSource, null, null,
				true));
		$this->file = new CommonFile($fileSource, 'huii.png');

	}

	function tearDown(): void {
		$this->tempDirFsPath->delete();
	}

	function testGetOrCreateThumb(): void {
		$imageFile = new ImageFile($this->file);

		$thumbImageFile = $imageFile->getOrCreateThumb(ThSt::prop(6, 6));
		$this->assertEquals(6, $thumbImageFile->getWidth());
		$this->assertEquals(6, $thumbImageFile->getHeight());
		$this->assertEquals('12x12.png', $thumbImageFile->getFile()->getFileSource()->getFsPath()->getName());

		$thumbImageFile = $imageFile->getOrCreateThumb(ThSt::prop(6, 6, imageMimeType: ImageMimeType::WEBP));
		$this->assertEquals(6, $thumbImageFile->getWidth());
		$this->assertEquals(6, $thumbImageFile->getHeight());
		$this->assertEquals('12x12.png.webp', $thumbImageFile->getFile()->getFileSource()->getFsPath()->getName());

		// validate if the file is really saved in webp
		$imageSource = ImageSourceFactory::createFromFileName($thumbImageFile->getFile()->getFileSource()->getFsPath(),
						ImageMimeType::WEBP->value)
				->createImageResource();
		$this->assertEquals(6, $imageSource->getHeight());
		$imageSource->destroy();
	}

}