<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use pocketmine\nbt\tag\CompoundTag;

final class ClipboardEntry{

	public function __construct(
		public int $full_block,
		public ?CompoundTag $tile_nbt
	){}
}
