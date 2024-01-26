<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\clipboard;

use pocketmine\nbt\tag\CompoundTag;

final class ClipboardEntry{

	public function __construct(
		readonly public int $block_state_id,
		readonly public ?CompoundTag $tile_nbt
	){}
}