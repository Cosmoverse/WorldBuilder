<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;

interface EditorFormat{

	public function import(string $contents) : Clipboard;

	public function export(Clipboard $schematic) : string;
}