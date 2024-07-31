<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\format\mcschematic\MinecraftSchematicReadableEditorFormat;
use InvalidArgumentException;

final class EditorFormatRegistry{

	/** @var array<string, ReadableEditorFormat|WritableEditorFormat> */
	private array $formats = [];

	public function __construct(){
		$this->register(EditorFormatIds::MINECRAFT_SCHEMATIC, new MinecraftSchematicReadableEditorFormat());
	}

	public function register(string $identifier, ReadableEditorFormat|WritableEditorFormat $format) : void{
		$this->formats[$identifier] = $format;
	}

	public function get(string $identifier) : ReadableEditorFormat|WritableEditorFormat{
		if(!isset($this->formats[$identifier])){
			throw new InvalidArgumentException("Invalid editor format: " . $identifier);
		}

		return $this->formats[$identifier];
	}

	/**
	 * @return array<string, ReadableEditorFormat|WritableEditorFormat>
	 */
	public function getFormats() : array{
		return $this->formats;
	}
}