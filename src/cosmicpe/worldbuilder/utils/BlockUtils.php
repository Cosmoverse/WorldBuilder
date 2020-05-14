<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\LegacyStringToItemParser;

final class BlockUtils{

	public static function fromString(string $string) : ?Block{
		if(strpos($string, ":") !== false){
			[$id, $meta] = explode(":", $string);
			try{
				return BlockFactory::getInstance()->get((int) $id, (int) $meta);
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