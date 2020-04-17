<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

final class ReplacementMap{

	/** @var int[] */
	private $full_id_map = [];

	public function put(Block $find, Block $replace) : ReplacementMap{
		return $this->putFullId($find->getFullId(), $replace->getFullId());
	}

	public function putFullId(int $find, int $replace) : ReplacementMap{
		if($find !== $replace){
			$this->full_id_map[$find] = $replace;
		}
		return $this;
	}

	public function contains(Block $block) : bool{
		return isset($this->full_id_map[$block->getFullId()]);
	}

	public function isEmpty() : bool{
		return count($this->full_id_map) === 0;
	}

	public function toFullIdArray() : array{
		return $this->full_id_map;
	}

	public function __toString() : string{
		$result = "";
		foreach($this->full_id_map as $find => $replace){
			$result .= BlockFactory::fromFullBlock($find)->getName() . " -> " . BlockFactory::fromFullBlock($replace)->getName() . ", ";
		}
		return rtrim($result, ", ");
	}
}