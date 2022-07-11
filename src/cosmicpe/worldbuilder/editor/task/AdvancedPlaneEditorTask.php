<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use Generator;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;

abstract class AdvancedPlaneEditorTask extends EditorTask{

	public function run() : Generator{
		$first = $this->selection->getPoint(0);
		$second = $this->selection->getPoint(1);

		$min = Vector3::minComponents($first, $second);
		$min_x = $min->x;
		$min_z = $min->z;

		$max = Vector3::maxComponents($first, $second);
		$max_x = $max->x;
		$max_z = $max->z;

		$min_chunkX = $min_x >> Chunk::COORD_BIT_SIZE;
		$max_chunkX = $max_x >> Chunk::COORD_BIT_SIZE;
		$min_chunkZ = $min_z >> Chunk::COORD_BIT_SIZE;
		$max_chunkZ = $max_z >> Chunk::COORD_BIT_SIZE;

		$cursor = new ChunkIteratorCursor($this->getWorld());
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
				for($cursor->x = $min_i; $cursor->x <= $max_i; ++$cursor->x){
					for($cursor->z = $min_k; $cursor->z <= $max_k; ++$cursor->z){
						if($this->onIterate($cursor)){
							$changed = true;
						}
						yield true;
					}
				}

				if($changed){
					$this->onChunkChanged($cursor);
				}
			}
		}
	}

	/**
	 * @param ChunkIteratorCursor $cursor
	 * @return bool whether chunk was changed
	 */
	abstract protected function onIterate(ChunkIteratorCursor $cursor) : bool;
}