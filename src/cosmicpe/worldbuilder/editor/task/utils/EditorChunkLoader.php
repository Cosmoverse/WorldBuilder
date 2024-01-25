<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\ChunkLoader;

final class EditorChunkLoader implements ChunkLoader{

	public static function instance() : self{
		static $instance = new self();
		return $instance;
	}

	private function __construct(){
	}
}