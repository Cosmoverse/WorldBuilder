<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\replacement;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

final class BlockToBlockReplacementMap{

	/** @var int[] */
	private array $full_id_map = [];

	public function put(Block $find, Block $replace) : BlockToBlockReplacementMap{
		return $this->putFullId($find->getFullId(), $replace->getFullId());
	}

	public function putFullId(int $find, int $replace) : BlockToBlockReplacementMap{
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

	/**
	 * @return int[]
	 */
	public function toFullIdArray() : array{
		return $this->full_id_map;
	}

	public function __toString() : string{
		$block_factory = BlockFactory::getInstance();
		$result = "";
		foreach($this->full_id_map as $find => $replace){
			$result .= $block_factory->fromFullBlock($find)->getName() . " -> " . $block_factory->fromFullBlock($replace)->getName() . ", ";
		}
		return rtrim($result, ", ");
	}
}