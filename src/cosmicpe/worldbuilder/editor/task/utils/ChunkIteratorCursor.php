<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class ChunkIteratorCursor{

	/** @var World */
	public $world;

	/** @var int */
	public $chunkX;

	/** @var int */
	public $chunkZ;

	/** @var Chunk */
	public $chunk;

	/** @var int */
	public $x; // 0 -15

	/** @var int */
	public $z; // 0 - 15

	public function __construct(World $world){
		$this->world = $world;
	}
}