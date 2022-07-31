<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\replacement;

use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

final class BlockToWeightedRandomSelectorReplacementMap{

	/** @var array<int, WeightedRandomIntegerSelector> */
	private array $block_to_selector_map = [];

	public function put(Block $find, WeightedRandomIntegerSelector $selector) : self{
		$this->block_to_selector_map[$find->getFullId()] = $selector;
		return $this;
	}

	public function get(Block $block) : ?WeightedRandomIntegerSelector{
		return $this->block_to_selector_map[$block->getFullId()] ?? null;
	}

	public function contains(Block $block) : bool{
		return isset($this->block_to_selector_map[$block->getFullId()]);
	}

	public function isEmpty() : bool{
		return count($this->block_to_selector_map) === 0;
	}

	/**
	 * @return array<int, WeightedRandomIntegerSelector>
	 */
	public function toFullIdArray() : array{
		return $this->block_to_selector_map;
	}

	public function __toString() : string{
		$block_factory = BlockFactory::getInstance();
		$result = "";
		foreach($this->block_to_selector_map as $find => $replace){
			$result .= $block_factory->fromFullBlock($find)->getName() . " -> Random({" . $replace->count() . "}), ";
		}
		return rtrim($result, ", ");
	}
}