<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToWeightedRandomSelectorReplacementMap;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\world\World;

class ReplaceSetRandomEditorTask extends AdvancedEditorTask{

	/** @var WeightedRandomIntegerSelector[] */
	private array $replacement_map;

	public function __construct(World $world, Selection $selection, BlockToWeightedRandomSelectorReplacementMap $replacement_map){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->replacement_map = $replacement_map->toFullIdArray();
	}

	/**
	 * @return WeightedRandomIntegerSelector[]
	 */
	public function getReplacementMap() : array{
		return $this->replacement_map;
	}

	public function getName() : string{
		return "replacesetrandom";
	}

	protected function onIterate(SubChunkIteratorCursor $cursor) : bool{
		if(isset($this->replacement_map[$find = $cursor->sub_chunk->getFullBlock($cursor->x, $cursor->y, $cursor->z)])){
			$cursor->sub_chunk->setFullBlock($cursor->x, $cursor->y, $cursor->z, $this->replacement_map[$find]->generate(1)->current());
			$tile = $cursor->chunk->getTile($cursor->x, ($cursor->subChunkY << 4) + $cursor->y, $cursor->z);
			if($tile !== null){
				$cursor->chunk->removeTile($tile);
				// $tile->onBlockDestroyed();
			}
		}
		return true;
	}
}