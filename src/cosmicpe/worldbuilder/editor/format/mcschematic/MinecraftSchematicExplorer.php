<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format\mcschematic;

use cosmicpe\worldbuilder\editor\utils\schematic\SchematicEntry;
use pocketmine\block\BlockFactory;
use pocketmine\nbt\tag\CompoundTag;

final class MinecraftSchematicExplorer{

	public static function index(int $x, int $y, int $z, int $length, int $width) : int{
		return ($y * $length + $z) * $width + $x;
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @param int $length
	 * @param string $block_ids
	 * @param string $block_metas
	 * @param CompoundTag[] $tile_entities
	 */
	public function __construct(
		public int $width,
		public int $height,
		public int $length,
		public string $block_ids,
		public string $block_metas,
		public array $tile_entities
	){}

	public function indexAt(int $x, int $y, int $z) : int{
		return self::index($x, $y, $z, $this->length, $this->width);
	}

	public function getSchematicEntryAt(int $x, int $y, int $z) : ?SchematicEntry{
		return $this->getSchematicEntry($this->indexAt($x, $y, $z));
	}

	public function getSchematicEntry(int $index) : ?SchematicEntry{
		return isset($this->block_ids[$index]) ? new SchematicEntry(
			BlockFactory::getInstance()->get(ord($this->block_ids[$index]), ord($this->block_metas[$index]))->getFullId(),
			$this->tile_entities[$index] ?? null
		) : null;
	}
}