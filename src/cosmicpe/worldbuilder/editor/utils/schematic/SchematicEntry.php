<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use pocketmine\nbt\tag\CompoundTag;

final class SchematicEntry{

	public function __construct(
		readonly public int $block_state_id,
		readonly public ?CompoundTag $tile_nbt
	){}
}