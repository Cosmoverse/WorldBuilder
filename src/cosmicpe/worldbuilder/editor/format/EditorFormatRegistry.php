<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use InvalidArgumentException;

final class EditorFormatRegistry{

	/** @var array<string, EditorFormat> */
	private array $formats = [];

	public function __construct(){
	}

	public function register(string $identifier, EditorFormat $format) : void{
		$this->formats[$identifier] = $format;
	}

	public function get(string $identifier) : EditorFormat{
		if(!isset($this->formats[$identifier])){
			throw new InvalidArgumentException("Invalid editor format: " . $identifier);
		}

		return $this->formats[$identifier];
	}

	/**
	 * @return array<string, EditorFormat>
	 */
	public function getFormats() : array{
		return $this->formats;
	}
}