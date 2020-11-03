<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\math\Vector3;

interface Schematic{

	public function getWidth() : int;

	public function getHeight() : int;

	public function getLength() : int;

	public function getVolume() : int;

	public function asSelection(Vector3 $relative_to) : Selection;

	public function getRelativePosition() : Vector3;

	public function get(int $x, int $y, int $z) : ?SchematicEntry;

	public function copy(int $x, int $y, int $z, SchematicEntry $entry) : void;

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Generator|SchematicEntry[]
	 */
	public function getAll(&$x, &$y, &$z) : Generator;
}