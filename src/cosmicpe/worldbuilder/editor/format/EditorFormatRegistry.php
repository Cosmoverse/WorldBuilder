<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\format\mcschematic\MinecraftSchematicEditorFormat;
use InvalidArgumentException;

final class EditorFormatRegistry{

	/**
	 * @var EditorFormat[]
	 * @phpstan-var array<string, EditorFormat>
	 */
	private $formats = [];

	public function __construct(){
		$this->register(EditorFormatIds::MINECRAFT_SCHEMATIC, new MinecraftSchematicEditorFormat());
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
	 * @return EditorFormat[]
	 * @phpstan-return array<string, EditorFormat>
	 */
	public function getFormats() : array{
		return $this->formats;
	}
}