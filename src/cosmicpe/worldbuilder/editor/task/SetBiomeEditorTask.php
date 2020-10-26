<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\world\World;

class SetBiomeEditorTask extends AdvancedPlaneEditorTask{

	/** @var int */
	private $biome_id;

	public function __construct(World $world, Selection $selection, int $biome_id){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->biome_id = $biome_id;
	}

	final public function getBiomeId() : int{
		return $this->biome_id;
	}

	public function getName() : string{
		return "setbiome";
	}

	protected function onIterate(ChunkIteratorCursor $cursor) : bool{
		$cursor->chunk->setBiomeId($cursor->x, $cursor->z, $this->biome_id);
		return true;
	}
}