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

	/** @var int */
	public $width;

	/** @var int */
	public $height;

	/** @var int */
	public $length;

	/** @var string */
	public $block_ids;

	/** @var string */
	public $block_metas;

	/** @var CompoundTag[] */
	public $tile_entities;

	/**
	 * @param int $width
	 * @param int $height
	 * @param int $length
	 * @param string $block_ids
	 * @param string $block_metas
	 * @param CompoundTag[] $tile_entities
	 */
	public function __construct(int $width, int $height, int $length, string $block_ids, string $block_metas, array $tile_entities){
		$this->width = $width;
		$this->height = $height;
		$this->length = $length;
		$this->block_ids = $block_ids;
		$this->block_metas = $block_metas;
		$this->tile_entities = $tile_entities;
	}

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