<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\task\utils\EditorTaskUtils;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use Generator;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Traverser;

class SetBiomeEditorTask extends EditorTask{

	readonly private int $biome_id;

	public function __construct(World $world, Selection $selection, int $biome_id, bool $generate_new_chunks){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)), $generate_new_chunks);
		$this->biome_id = $biome_id;
	}

	final public function getBiomeId() : int{
		return $this->biome_id;
	}

	public function getName() : string{
		return "setbiome";
	}

	public function run() : Generator{
		$traverser = new Traverser(EditorTaskUtils::iterateChunks($this->world, $this->selection, $this->generate_new_chunks));
		while(yield from $traverser->next($cursor)){
			foreach($cursor->chunk->getSubChunks() as $y => $sub_chunk){
				$cursor->chunk->setSubChunk($y, new SubChunk(
					$sub_chunk->getEmptyBlockId(),
					$sub_chunk->getBlockLayers(),
					new PalettedBlockArray($this->biome_id),
					$sub_chunk->getBlockSkyLightArray(),
					$sub_chunk->getBlockLightArray()
				));
			}
			$this->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
			yield null => Traverser::VALUE;
		}
	}
}