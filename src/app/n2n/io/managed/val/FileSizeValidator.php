<?php
namespace n2n\io\managed\val;

use n2n\validation\validator\impl\SimpleValidatorAdapter;
use n2n\l10n\Message;
use n2n\validation\plan\Validatable;
use n2n\util\magic\MagicContext;
use n2n\util\type\TypeConstraint;
use n2n\io\managed\File;
use n2n\util\type\CastUtils;
use n2n\validation\lang\ValidationMessages;

class FileSizeValidator extends SimpleValidatorAdapter {
	private int $maxSize;
	
	function __construct(int $maxSize, ?Message $errorMessage = null) {
		parent::__construct($errorMessage);
		$this->maxSize = $maxSize;
	}
	
	function getMaxSize(): int {
		return $this->maxSize;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function testSingle(Validatable $validatable, MagicContext $magicContext): bool {
		$file = $this->readSafeValue($validatable, TypeConstraint::createSimple(File::class));
		if ($file !== null) {
			CastUtils::assertTrue($file instanceof File);
		}
		
		return $file === null || FileValidationUtils::sizeAllowed($file, $this->maxSize);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function validateSingle(Validatable $validatable, MagicContext $magicContext): void {
		if ($this->testSingle($validatable, $magicContext)) {
			return;
		}

		$file = $this->readSafeValue($validatable, TypeConstraint::createSimple(File::class));
		CastUtils::assertTrue($file instanceof File);

		$validatable->addError(ValidationMessages::fileSize($file, $this->maxSize,
				$this->readLabel($validatable)));
	}
}

