<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\world\World;

class SetRandomEditorTask extends AdvancedEditorTask{

	private WeightedRandomIntegerSelector $selector;

	public function __construct(World $world, Selection $selection, WeightedRandomIntegerSelector $selector){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->selector = $selector;
	}

	public function getName() : string{
		return "setrandom";
	}

	protected function onIterate(SubChunkIteratorCursor $cursor) : bool{
		$cursor->sub_chunk->setFullBlock($cursor->x, $cursor->y, $cursor->z, $this->selector->generate(1)->current());
		$tile = $cursor->chunk->getTile($cursor->x, ($cursor->subChunkY << 4) + $cursor->y, $cursor->z);
		if($tile !== null){
			$cursor->chunk->removeTile($tile);
			// $tile->onBlockDestroyed();
		}
		return true;
	}
}