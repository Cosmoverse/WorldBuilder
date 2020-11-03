<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format\mcschematic;

use cosmicpe\worldbuilder\editor\format\EditorFormat;
use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use pocketmine\block\BlockFactory;
use pocketmine\block\tile\Tile;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;

class MinecraftSchematicEditorFormat implements EditorFormat{

	private const TAG_WIDTH = "Width"; // short
	private const TAG_HEIGHT = "Height"; // short
	private const TAG_LENGTH = "Length"; // short
	private const TAG_BLOCK_IDS = "Blocks"; // byte array
	private const TAG_BLOCK_METAS = "Data"; // byte array
	private const TAG_TILE_ENTITIES = "TileEntities"; // list

	public function import(string $contents) : Schematic{
		$root = (new BigEndianNbtSerializer())->read(zlib_decode($contents))->mustGetCompoundTag();

		$width = $root->getShort(self::TAG_WIDTH);
		$height = $root->getShort(self::TAG_HEIGHT);
		$length = $root->getShort(self::TAG_LENGTH);

		$block_ids = $root->getByteArray(self::TAG_BLOCK_IDS);
		$block_metas = $root->getByteArray(self::TAG_BLOCK_METAS);

		$explorer = new MinecraftSchematicExplorer($width, $height, $length, $block_ids, $block_metas, []);
		/** @var CompoundTag $tag */
		foreach($root->getListTag(self::TAG_TILE_ENTITIES) as $tag){
			$explorer->tile_entities[$explorer->indexAt($tag->getInt(Tile::TAG_X), $tag->getInt(Tile::TAG_Y), $tag->getInt(Tile::TAG_Z))] = $tag;
		}

		return new LazyLoadedMinecraftSchematic(new Vector3(0, 0, 0), new Vector3(0, 0, 0), new Vector3($width - 1, $height - 1, $length - 1), $explorer);
	}

	public function export(Schematic $schematic) : string{
		$width = $schematic->getWidth();
		$height = $schematic->getHeight();
		$length = $schematic->getLength();

		$block_ids = "";
		$block_metas = "";
		$tile_entities = new ListTag([], NBT::TAG_Compound);

		$block_factory = BlockFactory::getInstance();
		for($j = 0; $j < $height; ++$j){
			for($k = 0; $k < $length; ++$k){
				for($i = 0; $i < $width; ++$i){
					$entry = $schematic->get($i, $j, $k);
					if($entry !== null){
						$block = $block_factory->fromFullBlock($entry->full_block);
						$id = $block->getId();
						$meta = $block->getMeta();
					}else{
						$id = 0;
						$meta = 0;
					}

					$block_ids .= chr($id);
					$block_metas .= chr($meta);

					if($entry->tile_nbt !== null){
						$tile_entities->push($entry->tile_nbt);
					}
				}
			}
		}

		return zlib_encode((new BigEndianNbtSerializer())->write(new TreeRoot(
			CompoundTag::create()
				->setShort(self::TAG_WIDTH, $schematic->getWidth())
				->setShort(self::TAG_HEIGHT, $schematic->getHeight())
				->setShort(self::TAG_LENGTH, $schematic->getLength())
				->setByteArray(self::TAG_BLOCK_IDS, $block_ids)
				->setByteArray(self::TAG_BLOCK_METAS, $block_metas)
				->setTag(self::TAG_TILE_ENTITIES, $tile_entities)
		)), ZLIB_ENCODING_DEFLATE, 7);
	}
}