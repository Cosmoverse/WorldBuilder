<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;

interface EditorFormat{

	public function import(string $contents) : Schematic;

	public function export(Schematic $schematic) : string;
}