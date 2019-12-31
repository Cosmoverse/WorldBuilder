<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\editor\task;

use cosmicpe\buildertools\editor\utils\ReplacementMap;
use cosmicpe\buildertools\session\utils\Selection;
use cosmicpe\buildertools\utils\Vector3Utils;
use pocketmine\world\World;

class ReplaceEditorTask extends AdvancedEditorTask{

	/** @var int[] */
	private $replacement_map;

	public function __construct(World $world, Selection $selection, ReplacementMap $replacement_map){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->replacement_map = $replacement_map->toFullIdArray();
	}

	public function getName() : string{
		return "replace";
	}

	protected function onIterate(int $x, int $y, int $z) : bool{
		if(isset($this->replacement_map[$find = $this->iterator->currentSubChunk->getFullBlock($x, $y & 0x0f, $z)])){
			$this->iterator->currentSubChunk->setFullBlock($x, $y & 0x0f, $z, $this->replacement_map[$find]);
			$tile = $this->iterator->currentChunk->getTile($x, $y, $z);
			if($tile !== null){
				$tile->onBlockDestroyed();
			}
			return true;
		}
		return false;
	}
}