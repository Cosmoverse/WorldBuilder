<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

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

		for($x = $min_x; $x <= $max_x; ++$x){
			for($z = $min_z; $z <= $max_z; ++$z){
				if($this->onIterate($x, $z)){
					$this->onChunkChanged($x, $z);
				}
				yield true;
			}
		}
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @return bool whether chunk was changed
	 */
	abstract protected function onIterate(int $x, int $z) : bool;
}