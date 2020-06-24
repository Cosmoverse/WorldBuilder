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

		for($x = $min_x; $x <= $max_x; ++$x){
			$chunkX = $x >> 4;
			$subChunkX = $x & 0x0f;
			for($z = $min_z; $z <= $max_z; ++$z){
				$chunkZ = $z >> 4;
				$subChunkZ = $z & 0x0f;
				$changed = false;
				for($y = $min_y; $y <= $max_y; ++$y){
					if(!$this->iterator->moveTo($x, $y, $z, true)){
						break;
					}

					if($this->onIterate($chunkX, $chunkZ, $subChunkX, $y, $subChunkZ)){
						$changed = true;
					}
					yield true;
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