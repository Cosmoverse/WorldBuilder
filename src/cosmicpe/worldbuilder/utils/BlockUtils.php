<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\item\LegacyStringToItemParser;

final class BlockUtils{

	public static function fromString(string $string) : ?Block{
		if(($id_meta_split_pos = strrpos($string, ":")) !== false){
			$id = substr($string, 0, $id_meta_split_pos);
			if(!is_numeric($id) && strpos($id, "minecraft:") !== 0){
				$id = "minecraft:{$id}";
			}

			$meta = substr($string, $id_meta_split_pos + 1);

			try{
				return BlockFactory::getInstance()->get(LegacyBlockIdToStringIdMap::getInstance()->stringToLegacy($id) ?? (int) $id, (int) $meta);
			}catch(InvalidArgumentException $e){
				return null;
			}
		}

		try{
			$block = LegacyStringToItemParser::getInstance()->parse($string)->getBlock();
		}catch(InvalidArgumentException $e){
			return null;
		}
		return $block->canBePlaced() || ($block->getId() === BlockLegacyIds::AIR && ($string === "air" || $string === "0")) ? $block : null;
	}
}