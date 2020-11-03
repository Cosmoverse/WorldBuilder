<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\schematic;

use pocketmine\nbt\tag\CompoundTag;

final class SchematicEntry{

	/** @var int */
	public $full_block;

	/** @var CompoundTag|null */
	public $tile_nbt;

	public function __construct(int $full_block, ?CompoundTag $tile_nbt){
		$this->full_block = $full_block;
		$this->tile_nbt = $tile_nbt;
	}
}