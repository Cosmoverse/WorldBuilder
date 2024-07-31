<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format\mcschematic;

use cosmicpe\worldbuilder\editor\format\ReadableEditorFormat;
use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;
use cosmicpe\worldbuilder\editor\utils\clipboard\InMemoryClipboard;
use cosmicpe\worldbuilder\utils\PcPEBlockMapping;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use ReflectionProperty;
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

		$air = VanillaBlocks::AIR()->getStateId();

		$translation_mapping = PcPEBlockMapping::getFullIdMapping();
		for($i = 0, $count = strlen($block_ids); $i < $count; $i++){
			$id = ord($block_ids[$i]);
			$meta = ord($block_metas[$i]);
			$translation = PcPEBlockMapping::translate($translation_mapping, $id, $meta, $i);
			if($translation !== null){
				[$id, $meta] = $translation;
			}
			try{
				$block = $deserializer->deserialize($upgrader->upgradeIntIdMeta($id, $meta));
			}catch(BlockStateDeserializeException){
				$block = VanillaBlocks::INFO_UPDATE()->getStateId();
			}
			if($block === $air){
				continue;
			}
			$blocks[$i] = $block;
		}

		$explorer = new MinecraftSchematicExplorer($width, $height, $length, $blocks, []);
		$known_tiles = (new ReflectionProperty(TileFactory::class, "knownTiles"))->getValue(TileFactory::getInstance());
		/** @var CompoundTag $tag */
		foreach($root->getListTag(self::TAG_TILE_ENTITIES) as $tag){
			if($tag->getTag("id") === null || !isset($known_tiles[$tag->getString("id")])){
				continue;
			}
			[$x, $y, $z] = $tag->getIntArray("Pos");
			$explorer->tile_entities[$explorer->indexAt($x, $y, $z)] = $tag;
		}

		return new LazyLoadedMinecraftSchematic(new InMemoryClipboard(new Vector3(0, 0, 0), new Vector3(0, 0, 0), new Vector3($width - 1, $height - 1, $length - 1)), $explorer);
	}
}