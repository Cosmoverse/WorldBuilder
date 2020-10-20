<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\format\SubChunk;

class SubChunkIteratorCursor extends ChunkIteratorCursor{

	/** @var int */
	public $subChunkY;

	/** @var SubChunk */
	public $sub_chunk;

	/** @var int */
	public $y; // 0 - 15
}