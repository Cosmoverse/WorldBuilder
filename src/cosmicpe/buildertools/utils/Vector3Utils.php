<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\utils;

use pocketmine\math\Vector3;

final class Vector3Utils{

	/**
	 * Returns the volume in blocks^3 occupied by a cuboid made by
	 * two Vector3s.
	 *
	 * @param Vector3 $a
	 * @param Vector3 $b
	 * @return float
	 */
	public static function calculateVolume(Vector3 $a, Vector3 $b) : float{
		$min = Vector3::minComponents($a, $b);
		$max = Vector3::maxComponents($a, $b);
		return (1 + $max->x - $min->x) * (1 + $max->y - $min->y) * (1 + $max->z - $min->z);
	}
}