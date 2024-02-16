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
use n2n\validation\plan\ValidationContext;

class FileMimeTypeValidator extends SimpleValidatorAdapter {
	private $allowedMimeTypes;
	
	function __construct(array $allowedMimeTypes, Message $errorMessage = null) {
		parent::__construct($errorMessage);

		$this->allowedMimeTypes = $allowedMimeTypes;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function testSingle(Validatable $validatable, ValidationContext $validationContext, MagicContext $magicContext): bool {
		$file = $this->readSafeValue($validatable, TypeConstraint::createSimple(File::class));
		if (null !== $file) {
			CastUtils::assertTrue($file instanceof File);
		}
		
		return $file === null || FileValidationUtils::mimeTypeAllowed($file, $this->allowedMimeTypes);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function validateSingle(Validatable $validatable, MagicContext $magicContext): void {
		if (!$this->testSingle($validatable, $magicContext)) {
			$file = $this->readSafeValue($validatable, TypeConstraint::createSimple(File::class));
			CastUtils::assertTrue($file instanceof File);
			
			$validatable->addError(ValidationMessages::fileType($file, $this->allowedMimeTypes, 
					$this->readLabel($validatable)));
		}
	}
}