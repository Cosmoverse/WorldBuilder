<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;

final class SubChunkIteratorCursor{

	/**
	 * @param int<0, 15> $x
	 * @param int<0, 15> $y
	 * @param int<0, 15> $z
	 * @param int $chunk_x
	 * @param int $chunk_z
	 * @param int<Chunk::MIN_SUBCHUNK_INDEX, Chunk::MAX_SUBCHUNK_INDEX> $sub_chunk_y
	 * @param SubChunk $sub_chunk
	 * @param Chunk $chunk
	 */
	public function __construct(
		readonly public int $x,
		readonly public int $y,
		readonly public int $z,
		readonly public int $chunk_x,
		readonly public int $chunk_z,
		readonly public int $sub_chunk_y,
		readonly public SubChunk $sub_chunk,
		readonly public Chunk $chunk
	){}
}