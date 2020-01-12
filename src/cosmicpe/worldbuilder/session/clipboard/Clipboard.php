<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session\clipboard;

use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\math\Vector3;
use pocketmine\world\World;

final class Clipboard{

	/** @var Selection */
	private $selection;

	/** @var Vector3 */
	private $relative_position;

	/** @var ClipboardEntry[] */
	private $entries = [];

	public function __construct(Selection $selection, Vector3 $relative_position){
		$this->selection = $selection;
		$this->relative_position = $relative_position;
	}

	public function getSelection() : Selection{
		return $this->selection;
	}

	public function getRelativePosition() : Vector3{
		return $this->relative_position;
	}

	public function copy(int $x, int $y, int $z, ClipboardEntry $entry) : void{
		$this->entries[World::blockHash($x, $y, $z)] = $entry;
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