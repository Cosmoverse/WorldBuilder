<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session\clipboard;

use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\math\Vector3;
use pocketmine\world\World;

final class Clipboard{

	/** @var Vector3 */
	private $minimum;

	/** @var Vector3 */
	private $maximum;

	/** @var Vector3 */
	private $relative_position;

	/** @var ClipboardEntry[] */
	private $entries = [];

	public function __construct(Vector3 $relative_position){
		$this->relative_position = $relative_position;
		$this->minimum = new Vector3();
		$this->maximum = new Vector3();
	}

	public function asSelection(Vector3 $relative_to) : Selection{
		$selection = new Selection(2);
		$selection->setPoint(0, $this->minimum->add($relative_to));
		$selection->setPoint(1, $this->maximum->add($relative_to));
		return $selection;
	}

	public function getRelativePosition() : Vector3{
		return $this->relative_position;
	}

	public function copy(int $x, int $y, int $z, ClipboardEntry $entry) : void{
		$this->entries[World::blockHash($x, $y, $z)] = $entry;

		if($x > $this->maximum->x){
			$this->maximum->x = $x;
		}
		if($x < $this->minimum->x){
			$this->minimum->x = $x;
		}

		if($y > $this->maximum->y){
			$this->maximum->y = $y;
		}
		if($y < $this->minimum->y){
			$this->minimum->y = $y;
		}

		if($z > $this->maximum->z){
			$this->maximum->z = $z;
		}
		if($z < $this->minimum->z){
			$this->minimum->z = $z;
		}
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Generator|ClipboardEntry[]
	 */
	public function getAll(&$x, &$y, &$z) : Generator{
		foreach($this->entries as $hash => $entry){
			World::getBlockXYZ($hash, $x, $y, $z);
			yield $entry;
		}
	}

	public function getVolume() : int{
		return count($this->entries);
	}
}