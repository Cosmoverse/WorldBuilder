<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use function max;
use function min;

final class MathUtils{

	public static function calculateVolume(int $x1, int $y1, int $z1, int $x2, int $y2, int $z2) : int{
		$dx = max($x1, $x2) - min($x1, $x2);
		$dy = max($y1, $y2) - min($y1, $y2);
		$dz = max($z1, $z2) - min($z1, $z2);
		return ($dx + 1) * ($dy + 1) * ($dz + 1);
	}
}