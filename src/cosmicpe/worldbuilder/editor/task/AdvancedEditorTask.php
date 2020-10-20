<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

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
		$min_chunkZ = $min_z >> 4;
		$max_chunkZ = $max_z >> 4;

		for($chunkX = $min_chunkX; $chunkX <= $max_chunkX; ++$chunkX){
			$abs_cx = $chunkX << 4;
			$min_i = max($abs_cx, $min_x) & 0x0f;
			$max_i = min($abs_cx + 0x0f, $max_x) & 0x0f;
			for($chunkZ = $min_chunkZ; $chunkZ <= $max_chunkZ; ++$chunkZ){
				$abs_cz = $chunkZ << 4;
				$min_k = max($abs_cz, $min_z) & 0x0f;
				$max_k = min($abs_cz + 0x0f, $max_z) & 0x0f;
				$changed = false;
				for($subChunkX = $min_i; $subChunkX <= $max_i; ++$subChunkX){
					for($subChunkZ = $min_k; $subChunkZ <= $max_k; ++$subChunkZ){
						for($y = $min_y; $y <= $max_y; ++$y){
							if(!$this->iterator->moveTo($abs_cx + $subChunkX, $y, $abs_cz + $subChunkZ, true)){
								break 3;
							}
							if($this->onIterate($chunkX, $chunkZ, $subChunkX, $y, $subChunkZ)){
								$changed = true;
							}
							yield true;
						}
					}
				}

				if($changed){
					$this->onChunkChanged($chunkX, $chunkZ);
				}
			}
		}
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 * @param int $x 0-15
	 * @param int $y 0-255
	 * @param int $z 0-15
	 * @return bool whether chunk was changed
	 */
	abstract protected function onIterate(int $chunkX, int $chunkZ, int $x, int $y, int $z) : bool;
}