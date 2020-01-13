<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy\nbtcopier;

use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;

class DefaultNamedtagCopier implements NamedTagCopier{

	public function copy(Tile $tile) : CompoundTag{
		return $tile->saveNBT();
	}

	public function moveTo(CompoundTag $nbt, int $x, int $y, int $z) : CompoundTag{
		return (clone $nbt)
			->setInt(Tile::TAG_X, $x)
			->setInt(Tile::TAG_Y, $y)
			->setInt(Tile::TAG_Z, $z);
	}
}