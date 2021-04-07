<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy\nbtcopier;

use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\nbt\tag\CompoundTag;

final class NamedtagCopierManager{

	/** @var NamedTagCopier[] */
	private static array $copiers = [];

	private static NamedTagCopier $default_copier;

	public static function init() : void{
		self::$default_copier = new DefaultNamedtagCopier();
		self::register(TileFactory::getInstance()->getSaveId(Chest::class), new ChestNamedtagCopier());
	}

	public static function register(string $save_id, NamedTagCopier $copier) : void{
		self::$copiers[$save_id] = $copier;
	}

	public static function get(string $save_id) : NamedTagCopier{
		return self::$copiers[$save_id] ?? self::$default_copier;
	}

	public static function copy(Tile $tile) : CompoundTag{
		return self::get(TileFactory::getInstance()->getSaveId(get_class($tile)))->copy($tile);
	}

	public static function moveTo(CompoundTag $nbt, int $x, int $y, int $z) : CompoundTag{
		return self::get($nbt->getString(Tile::TAG_ID))->moveTo($nbt, $x, $y, $z);
	}
}