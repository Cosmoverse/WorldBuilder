<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;

final class BlockUtils{

	public static function fromString(string $string) : ?Block{
		if($string === "air"){
			return VanillaBlocks::AIR();
		}
		try {
			$item = StringToItemParser::getInstance()->parse($string) ?? LegacyStringToItemParser::getInstance()->parse($string);
		}catch(LegacyStringToItemParserException){
			return null;
		}
		if(!$item->isNull() && $item->getBlock()->getTypeId() === BlockTypeIds::AIR){
			return null;
		}
		return $item->getBlock();
	}
}