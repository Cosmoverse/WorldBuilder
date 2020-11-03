<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use Generator;
use pocketmine\math\Vector3;

abstract class AdvancedChunkEditorTask extends EditorTask{

	public function run() : Generator{
		$first = $this->selection->getPoint(0);
		$second = $this->selection->getPoint(1);

		$min = Vector3::minComponents($first, $second);
		$min_x = $min->x >> 4;
		$min_z = $min->z >> 4;

		$max = Vector3::maxComponents($first, $second);
		$max_x = $max->x >> 4;
		$max_z = $max->z >> 4;

		$cursor = new ChunkIteratorCursor($this->getWorld());
		for($cursor->chunkX = $min_x; $cursor->chunkX <= $max_x; ++$cursor->chunkX){
			for($cursor->chunkZ = $min_z; $cursor->chunkZ <= $max_z; ++$cursor->chunkZ){
				$cursor->chunk = $cursor->world->getOrLoadChunk($cursor->chunkX, $cursor->chunkZ, false);
				if($cursor->chunk !== null && $this->onIterate($cursor)){
					$this->onChunkChanged($cursor);
				}
				yield true;
			}
		}
	}

	/**
	 * @param ChunkIteratorCursor $cursor
	 * @return bool whether chunk was changed
	 */
	abstract protected function onIterate(ChunkIteratorCursor $cursor) : bool;
}