<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format;

use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;

interface ReadableEditorFormat{

	public function read(string $contents) : Clipboard;
}