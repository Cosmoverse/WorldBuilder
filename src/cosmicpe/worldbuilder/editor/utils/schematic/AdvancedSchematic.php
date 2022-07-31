<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use pocketmine\math\Vector3;

abstract class AdvancedSchematic implements Schematic{

	public function __construct(
		protected Vector3 $relative_position,
		protected Vector3 $minimum,
		protected Vector3 $maximum
	){}

	public function getWidth() : int{
		return 1 + ($this->maximum->x - $this->minimum->x);
	}

	public function getHeight() : int{
		return 1 + ($this->maximum->y - $this->minimum->y);
	}

	public function getLength() : int{
		return 1 + ($this->maximum->z - $this->minimum->z);
	}

	public function getRelativePosition() : Vector3{
		return $this->relative_position;
	}
}