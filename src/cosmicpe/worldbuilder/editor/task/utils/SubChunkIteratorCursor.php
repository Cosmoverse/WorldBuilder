<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\format\SubChunk;

class SubChunkIteratorCursor extends ChunkIteratorCursor{

	public int $subChunkY;
	public SubChunk $sub_chunk;
	public int $y; // 0 - 15
}