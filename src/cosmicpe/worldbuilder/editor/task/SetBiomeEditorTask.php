<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\world\World;

class SetBiomeEditorTask extends AdvancedPlaneEditorTask{

	readonly private int $biome_id;
	readonly private int $min_y;
	readonly private int $max_y;

	public function __construct(World $world, Selection $selection, int $biome_id){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->biome_id = $biome_id;
		$this->min_y = $world->getMinY();
		$this->max_y = $world->getMaxY();
	}

	final public function getBiomeId() : int{
		return $this->biome_id;
	}

	public function getName() : string{
		return "setbiome";
	}

	protected function onIterate(ChunkIteratorCursor $cursor) : bool{
		for($y = $this->min_y; $y < $this->max_y; ++$y) {
			$cursor->chunk->setBiomeId($cursor->x, $y, $cursor->z, $this->biome_id);
		}
		return true;
	}
}