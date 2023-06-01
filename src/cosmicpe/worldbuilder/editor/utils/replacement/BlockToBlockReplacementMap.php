<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\replacement;

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;

final class BlockToBlockReplacementMap{

	/** @var array<int, int> */
	private array $full_id_map = [];

	public function put(Block $find, Block $replace) : BlockToBlockReplacementMap{
		return $this->putFullId($find->getStateId(), $replace->getStateId());
	}

	public function putFullId(int $find, int $replace) : BlockToBlockReplacementMap{
		if($find !== $replace){
			$this->full_id_map[$find] = $replace;
		}
		return $this;
	}

	public function contains(Block $block) : bool{
		return isset($this->full_id_map[$block->getStateId()]);
	}

	public function isEmpty() : bool{
		return count($this->full_id_map) === 0;
	}

	/**
	 * @return array<int, int>
	 */
	public function toFullIdArray() : array{
		return $this->full_id_map;
	}

	public function __toString() : string{
		$block_state_registry = RuntimeBlockStateRegistry::getInstance();
		$result = "";
		foreach($this->full_id_map as $find => $replace){
			$result .= $block_state_registry->fromStateId($find)->getName() . " -> " . $block_state_registry->fromStateId($replace)->getName() . ", ";
		}
		return rtrim($result, ", ");
	}
}