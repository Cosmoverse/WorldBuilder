<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy\nbtcopier;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;

class ChestNamedtagCopier extends DefaultNamedtagCopier{

	private const TAG_PAIRX_RELATIVE = "worldbuilder:pairx_rel";
	private const TAG_PAIRZ_RELATIVE = "worldbuilder:pairz_rel";

	public function copy(Tile $tile) : CompoundTag{
		$nbt = parent::copy($tile);
		if($nbt->hasTag(Chest::TAG_PAIRX) && $nbt->hasTag(Chest::TAG_PAIRZ)){
			$nbt
				->setInt(self::TAG_PAIRX_RELATIVE, $nbt->getInt(Chest::TAG_PAIRX) - $nbt->getInt(Tile::TAG_X))
				->setInt(self::TAG_PAIRZ_RELATIVE, $nbt->getInt(Chest::TAG_PAIRZ) - $nbt->getInt(Tile::TAG_Z));
		}
		return $nbt;
	}

	public function moveTo(CompoundTag $nbt, int $x, int $y, int $z) : CompoundTag{
		$nbt = parent::moveTo($nbt, $x, $y, $z);
		if($nbt->hasTag(self::TAG_PAIRX_RELATIVE) && $nbt->hasTag(self::TAG_PAIRZ_RELATIVE)){
			$nbt
				->setInt(Chest::TAG_PAIRX, $x + $nbt->getInt(self::TAG_PAIRX_RELATIVE))
				->setInt(Chest::TAG_PAIRZ, $z + $nbt->getInt(self::TAG_PAIRZ_RELATIVE))
				->removeTag(self::TAG_PAIRX_RELATIVE, self::TAG_PAIRZ_RELATIVE);
		}
		return $nbt;
	}
}