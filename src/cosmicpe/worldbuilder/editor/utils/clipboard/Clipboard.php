<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\clipboard;

use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\math\Vector3;

interface Clipboard{

	public function getWidth() : int;

	public function getHeight() : int;

	public function getLength() : int;

	public function calculateEntryCount() : int;

	public function asSelection(Vector3 $relative_to) : Selection;

	public function getRelativePosition() : Vector3;

	public function get(int $x, int $y, int $z) : ?ClipboardEntry;

	public function copy(int $x, int $y, int $z, ClipboardEntry $entry) : void;

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Generator<ClipboardEntry>
	 */
	public function getAll(&$x, &$y, &$z) : Generator;
}