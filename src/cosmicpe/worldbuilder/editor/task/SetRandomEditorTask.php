<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\world\World;

class SetRandomEditorTask extends AdvancedEditorTask{

	/** @var WeightedRandomIntegerSelector */
	private $selector;

	public function __construct(World $world, Selection $selection, WeightedRandomIntegerSelector $selector){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->selector = $selector;
	}

	public function getName() : string{
		return "setrandom";
	}

	protected function onIterate(int $chunkX, int $chunkZ, int $x, int $y, int $z) : bool{
		$this->iterator->currentSubChunk->setFullBlock($x, $y & 0x0f, $z, $this->selector->generate(1)->current());
		$tile = $this->iterator->currentChunk->getTile($x, $y, $z);
		if($tile !== null){
			$this->iterator->currentChunk->removeTile($tile);
			// $tile->onBlockDestroyed();
		}
		return true;
	}
}