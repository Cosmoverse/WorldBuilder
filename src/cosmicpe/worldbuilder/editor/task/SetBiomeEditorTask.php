<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\task\utils\EditorTaskUtils;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use Generator;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Traverser;

class SetBiomeEditorTask extends EditorTask{

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

	public function run() : Generator{
		$cursor = new ChunkIteratorCursor($this->world);
		foreach(EditorTaskUtils::iterateChunks($this->selection, $cursor) as $operation){
			for($y = $this->min_y; $y < $this->max_y; ++$y) {
				$cursor->chunk->setBiomeId($cursor->x, $y, $cursor->z, $this->biome_id);
			}
			$cursor->world->setChunk($cursor->chunkX, $cursor->chunkZ, $cursor->chunk);
			yield null => Traverser::VALUE;
		}
	}
}