<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use function max;
use function min;

final class EditorTaskUtils{

	public const OP_WRITE_BUFFER = 0;
	public const OP_WRITE_WORLD = 1;

	/**
	 * @param Selection $selection
	 * @param ChunkIteratorCursor $cursor
	 * @return Generator<self::OP_WRITE_WORLD>
	 */
	public static function iterateChunks(Selection $selection, ChunkIteratorCursor $cursor) : Generator{
		$first = $selection->getPoint(0);
		$second = $selection->getPoint(1);

		$min = Vector3::minComponents($first, $second);
		$min_x = $min->x >> Chunk::COORD_BIT_SIZE;
		$min_z = $min->z >> Chunk::COORD_BIT_SIZE;

		$max = Vector3::maxComponents($first, $second);
		$max_x = $max->x >> Chunk::COORD_BIT_SIZE;
		$max_z = $max->z >> Chunk::COORD_BIT_SIZE;

		for($cursor->chunkX = $min_x; $cursor->chunkX <= $max_x; ++$cursor->chunkX){
			for($cursor->chunkZ = $min_z; $cursor->chunkZ <= $max_z; ++$cursor->chunkZ){
				$chunk = $cursor->world->loadChunk($cursor->chunkX, $cursor->chunkZ);
				if($chunk !== null){
					$cursor->chunk = $chunk;
					yield self::OP_WRITE_WORLD;
				}
			}
		}
	}

	/**
	 * @param Selection $selection
	 * @param SubChunkIteratorCursor $cursor
	 * @return Generator<self::OP_WRITE_*>
	 */
	public static function iterateBlocks(Selection $selection, SubChunkIteratorCursor $cursor) : Generator{
		$first = $selection->getPoint(0);
		$second = $selection->getPoint(1);

		$min = Vector3::minComponents($first, $second);
		$min_x = $min->x;
		$min_y = $min->y;
		$min_z = $min->z;

		$max = Vector3::maxComponents($first, $second);
		$max_x = $max->x;
		$max_y = $max->y;
		$max_z = $max->z;

		$min_chunkX = $min_x >> Chunk::COORD_BIT_SIZE;
		$max_chunkX = $max_x >> Chunk::COORD_BIT_SIZE;
		$min_subChunkY = $min_y >> Chunk::COORD_BIT_SIZE;
		$max_subChunkY = $max_y >> Chunk::COORD_BIT_SIZE;
		$min_chunkZ = $min_z >> Chunk::COORD_BIT_SIZE;
		$max_chunkZ = $max_z >> Chunk::COORD_BIT_SIZE;

		for($cursor->chunkX = $min_chunkX; $cursor->chunkX <= $max_chunkX; ++$cursor->chunkX){
			$abs_cx = $cursor->chunkX << Chunk::COORD_BIT_SIZE;
			$min_i = max($abs_cx, $min_x) & Chunk::COORD_MASK;
			$max_i = min($abs_cx + Chunk::COORD_MASK, $max_x) & Chunk::COORD_MASK;
			for($cursor->chunkZ = $min_chunkZ; $cursor->chunkZ <= $max_chunkZ; ++$cursor->chunkZ){
				$chunk = $cursor->world->loadChunk($cursor->chunkX, $cursor->chunkZ);
				if($chunk === null){
					continue;
				}
				$cursor->chunk = $chunk;

				$abs_cz = $cursor->chunkZ << Chunk::COORD_BIT_SIZE;
				$min_k = max($abs_cz, $min_z) & Chunk::COORD_MASK;
				$max_k = min($abs_cz + Chunk::COORD_MASK, $max_z) & Chunk::COORD_MASK;

				for($cursor->subChunkY = $min_subChunkY; $cursor->subChunkY <= $max_subChunkY; ++$cursor->subChunkY){
					$cursor->sub_chunk = $cursor->chunk->getSubChunk($cursor->subChunkY);

					$abs_cy = $cursor->subChunkY << Chunk::COORD_BIT_SIZE;
					$min_j = max($abs_cy, $min_y) & Chunk::COORD_MASK;
					$max_j = min($abs_cy + Chunk::COORD_MASK, $max_y) & Chunk::COORD_MASK;
					for($cursor->y = $min_j; $cursor->y <= $max_j; ++$cursor->y){
						for($cursor->x = $min_i; $cursor->x <= $max_i; ++$cursor->x){
							for($cursor->z = $min_k; $cursor->z <= $max_k; ++$cursor->z){
								yield self::OP_WRITE_BUFFER;
							}
						}
					}
				}
				yield self::OP_WRITE_WORLD;
			}
		}
	}
}