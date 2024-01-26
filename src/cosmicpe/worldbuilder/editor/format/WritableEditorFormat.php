<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;

interface WritableEditorFormat{

	public function write(Clipboard $schematic) : string;
}