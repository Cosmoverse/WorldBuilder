<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class ChunkIteratorCursor{

	public int $chunkX = 0;
	public int $chunkZ = 0;
	public Chunk $chunk;
	public int $x = 0; // 0 - 15
	public int $z = 0; // 0 - 15

	public function __construct(
		readonly public World $world
	){}
}