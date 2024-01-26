<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format\mcschematic;

use cosmicpe\worldbuilder\editor\format\ReadableEditorFormat;
use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;
use pocketmine\block\tile\Tile;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use function ord;
use function strlen;

class MinecraftSchematicReadableEditorFormat implements ReadableEditorFormat{

	private const TAG_WIDTH = "Width"; // short
	private const TAG_HEIGHT = "Height"; // short
	private const TAG_LENGTH = "Length"; // short
	private const TAG_BLOCK_IDS = "Blocks"; // byte array
	private const TAG_BLOCK_METAS = "Data"; // byte array
	private const TAG_TILE_ENTITIES = "TileEntities"; // list

	public function read(string $contents) : Clipboard{
		$root = (new BigEndianNbtSerializer())->read(zlib_decode($contents))->mustGetCompoundTag();

		$width = $root->getShort(self::TAG_WIDTH);
		$height = $root->getShort(self::TAG_HEIGHT);
		$length = $root->getShort(self::TAG_LENGTH);

		$block_ids = $root->getByteArray(self::TAG_BLOCK_IDS);
		$block_metas = $root->getByteArray(self::TAG_BLOCK_METAS);
		$blocks = [];
		$deserializer = GlobalBlockStateHandlers::getDeserializer();
		$upgrader = GlobalBlockStateHandlers::getUpgrader();
		for($i = 0, $count = strlen($block_ids); $i < $count; $i++){
			try{
				$blocks[$i] = $deserializer->deserialize($upgrader->upgradeIntIdMeta(ord($block_ids[$i]), ord($block_metas[$i])));
			}catch(BlockStateDeserializeException){
				$blocks[$i] = VanillaBlocks::INFO_UPDATE()->getStateId();
			}
		}

		$explorer = new MinecraftSchematicExplorer($width, $height, $length, $blocks, []);
		/** @var CompoundTag $tag */
		foreach($root->getListTag(self::TAG_TILE_ENTITIES) as $tag){
			$explorer->tile_entities[$explorer->indexAt($tag->getInt(Tile::TAG_X), $tag->getInt(Tile::TAG_Y), $tag->getInt(Tile::TAG_Z))] = $tag;
		}

		return new LazyLoadedMinecraftSchematic(new Vector3(0, 0, 0), new Vector3(0, 0, 0), new Vector3($width - 1, $height - 1, $length - 1), $explorer);
	}
}