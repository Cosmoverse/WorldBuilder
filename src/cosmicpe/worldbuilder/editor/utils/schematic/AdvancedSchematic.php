<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use pocketmine\math\Vector3;

abstract class AdvancedSchematic implements Schematic{

	protected Vector3 $minimum;
	protected Vector3 $maximum;
	protected Vector3 $relative_position;

	public function __construct(Vector3 $relative_position, Vector3 $minimum, Vector3 $maximum){
		$this->relative_position = $relative_position;
		$this->minimum = $minimum;
		$this->maximum = $maximum;
	}

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