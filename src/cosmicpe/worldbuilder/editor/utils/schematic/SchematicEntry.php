<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;

final class SchematicEntry{

	public function __construct(
		public int $state_id,
		public ?CompoundTag $tile_nbt
	){}
}