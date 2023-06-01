<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\replacement;

use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;

final class BlockToWeightedRandomSelectorReplacementMap{

	/** @var array<int, WeightedRandomIntegerSelector> */
	private array $block_to_selector_map = [];

	public function put(Block $find, WeightedRandomIntegerSelector $selector) : self{
		$this->block_to_selector_map[$find->getStateId()] = $selector;
		return $this;
	}

	public function get(Block $block) : ?WeightedRandomIntegerSelector{
		return $this->block_to_selector_map[$block->getStateId()] ?? null;
	}

	public function contains(Block $block) : bool{
		return isset($this->block_to_selector_map[$block->getStateId()]);
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
		$block_state_registry = RuntimeBlockStateRegistry::getInstance();
		$result = "";
		foreach($this->block_to_selector_map as $find => $replace){
			$result .= $block_state_registry->fromStateId($find)->getName() . " -> Random({" . $replace->count() . "}), ";
		}
		return rtrim($result, ", ");
	}
}