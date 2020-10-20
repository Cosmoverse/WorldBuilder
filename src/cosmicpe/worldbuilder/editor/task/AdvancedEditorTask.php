<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use Generator;
use pocketmine\math\Vector3;

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

		$min_chunkX = $min_x >> 4;
		$max_chunkX = $max_x >> 4;
		$min_subChunkY = $min_y >> 4;
		$max_subChunkY = $max_y >> 4;
		$min_chunkZ = $min_z >> 4;
		$max_chunkZ = $max_z >> 4;

		$cursor = new SubChunkIteratorCursor($this->getWorld());
		for($cursor->chunkX = $min_chunkX; $cursor->chunkX <= $max_chunkX; ++$cursor->chunkX){
			$abs_cx = $cursor->chunkX << 4;
			$min_i = max($abs_cx, $min_x) & 0x0f;
			$max_i = min($abs_cx + 0x0f, $max_x) & 0x0f;
			for($cursor->chunkZ = $min_chunkZ; $cursor->chunkZ <= $max_chunkZ; ++$cursor->chunkZ){
				$cursor->chunk = $cursor->world->getOrLoadChunk($cursor->chunkX, $cursor->chunkZ);
				if($cursor->chunk === null){
					continue;
				}

				$changed = false;

				$abs_cz = $cursor->chunkZ << 4;
				$min_k = max($abs_cz, $min_z) & 0x0f;
				$max_k = min($abs_cz + 0x0f, $max_z) & 0x0f;

				for($cursor->subChunkY = $min_subChunkY; $cursor->subChunkY <= $max_subChunkY; ++$cursor->subChunkY){
					$cursor->sub_chunk = $cursor->chunk->getWritableSubChunk($cursor->subChunkY);

					$abs_cy = $cursor->subChunkY << 4;
					$min_j = max($abs_cy, $min_y) & 0x0f;
					$max_j = min($abs_cy + 0x0f, $max_y) & 0x0f;
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