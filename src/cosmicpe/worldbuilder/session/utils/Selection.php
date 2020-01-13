<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session\utils;

use pocketmine\math\Vector3;
use pocketmine\utils\Limits;
use pocketmine\world\World;
use SplFixedArray;

final class Selection{

	public static function cuboidalRadius(Vector3 $center, int $radius) : Selection{
		$center = $center->floor();
		$selection = new self(2);
		$selection->setPoint(0, $center->subtract($radius, $radius, $radius));
		$selection->setPoint(1, $center->add($radius, $radius, $radius));
		return $selection;
	}

	/** @var SplFixedArray<Vector3> */
	private $points;

	public function __construct(int $capacity){
		$this->points = new SplFixedArray($capacity);
	}

	public function getPoint(int $index) : Vector3{
		return $this->points[$index];
	}

	public function setPoint(int $index, ?Vector3 $point) : void{
		if($point !== null){
			$point->x = max(Limits::INT32_MIN, min(Limits::INT32_MAX, $point->x));
			$point->y = max(0, min(World::Y_MAX - 1, $point->y));
			$point->z = max(Limits::INT32_MIN, min(Limits::INT32_MAX, $point->z));
			$this->points[$index] = $point;
		}else{
			unset($this->points[$index]);
		}
	}

	/**
	 * @return Vector3[]
	 */
	public function getPoints() : array{
		return $this->points->toArray();
	}

	public function isComplete() : bool{
		foreach($this->points as $point){
			if($point === null){
				return false;
			}
		}
		return true;
	}
}