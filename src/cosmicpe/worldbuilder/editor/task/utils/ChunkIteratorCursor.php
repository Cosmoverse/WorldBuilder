<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\format\Chunk;

final class ChunkIteratorCursor{

	public function __construct(
		readonly public int $x,
		readonly public int $z,
		readonly public Chunk $chunk
	){}
}