<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use Generator;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;

abstract class AdvancedEditorTask extends EditorTask{

	public function run() : Generator{
		$first = $this->selection->getPoint(0);
		$second = $this->selection->getPoint(1);

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

		$cursor = new SubChunkIteratorCursor($this->world);
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

				$changed = false;

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
								if($this->onIterate($cursor)){
									$changed = true;
								}
								yield true;
							}
						}
					}
				}

				if($changed){
					$this->onChunkChanged($cursor);
				}
			}
		}
	}

	/**
	 * @param SubChunkIteratorCursor $cursor
	 * @return bool whether chunk was changed
	 */
	abstract protected function onIterate(SubChunkIteratorCursor $cursor) : bool;
}