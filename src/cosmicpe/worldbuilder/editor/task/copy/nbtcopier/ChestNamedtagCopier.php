<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy\nbtcopier;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class ChestNamedtagCopier extends DefaultNamedtagCopier{

	private const TAG_PAIRX_RELATIVE = "worldbuilder:pairx_rel";
	private const TAG_PAIRZ_RELATIVE = "worldbuilder:pairz_rel";

	public function copy(Tile $tile) : CompoundTag{
		$nbt = parent::copy($tile);
		if(($tag_pair_x = $nbt->getTag(Chest::TAG_PAIRX)) instanceof IntTag && ($tag_pair_z = $nbt->getTag(Chest::TAG_PAIRZ)) instanceof IntTag){
			$nbt
				->setInt(self::TAG_PAIRX_RELATIVE, $tag_pair_x->getValue() - $nbt->getInt(Tile::TAG_X))
				->setInt(self::TAG_PAIRZ_RELATIVE, $tag_pair_z->getValue() - $nbt->getInt(Tile::TAG_Z));
		}
		return $nbt;
	}

	public function moveTo(CompoundTag $nbt, int $x, int $y, int $z) : CompoundTag{
		$nbt = parent::moveTo($nbt, $x, $y, $z);
		if(($tag_pairx_rel = $nbt->getTag(self::TAG_PAIRX_RELATIVE)) instanceof IntTag && ($tag_pairz_rel = $nbt->getTag(self::TAG_PAIRZ_RELATIVE)) instanceof IntTag){
			$nbt
				->setInt(Chest::TAG_PAIRX, $x + $tag_pairx_rel->getValue())
				->setInt(Chest::TAG_PAIRZ, $z + $tag_pairz_rel->getValue())
				->removeTag(self::TAG_PAIRX_RELATIVE, self::TAG_PAIRZ_RELATIVE);
		}
		return $nbt;
	}
}