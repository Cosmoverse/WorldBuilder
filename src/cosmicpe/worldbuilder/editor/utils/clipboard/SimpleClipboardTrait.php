<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\clipboard;

use pocketmine\math\Vector3;

trait SimpleClipboardTrait{

	public function __construct(
		readonly private Vector3 $relative_position,
		readonly private Vector3 $minimum,
		readonly private Vector3 $maximum
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