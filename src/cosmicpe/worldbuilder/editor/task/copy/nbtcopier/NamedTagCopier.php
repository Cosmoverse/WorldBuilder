<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy\nbtcopier;

use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;

interface NamedTagCopier{

	/**
	 * Creates a copy of the tile's namedtag before
	 * storing it so it can be later pasted at a
	 * given coordinate.
	 *
	 * @param Tile $tile
	 * @return CompoundTag
	 */
	public function copy(Tile $tile) : CompoundTag;

	/**
	 * Modifies nbt properties before setting it as a
	 * tile in a world.
	 *
	 * @param CompoundTag $nbt
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return CompoundTag
	 */
	public function moveTo(CompoundTag $nbt, int $x, int $y, int $z) : CompoundTag;
}