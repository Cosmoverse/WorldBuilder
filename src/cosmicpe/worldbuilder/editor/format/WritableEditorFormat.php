<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;

interface WritableEditorFormat{

	public function write(Schematic $schematic) : string;
}