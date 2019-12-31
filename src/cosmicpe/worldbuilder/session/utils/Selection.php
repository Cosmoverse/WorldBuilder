<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session\utils;

use pocketmine\math\Vector3;
use SplFixedArray;

final class Selection{

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