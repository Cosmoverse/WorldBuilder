<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\EditorTaskUtils;
use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToWeightedRandomSelectorReplacementMap;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use Generator;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Traverser;
use function assert;

class ReplaceSetRandomEditorTask extends EditorTask{

	/** @var array<int, WeightedRandomIntegerSelector> */
	readonly private array $replacement_map;

	public function __construct(World $world, Selection $selection, BlockToWeightedRandomSelectorReplacementMap $replacement_map){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->replacement_map = $replacement_map->toFullIdArray();
	}

	/**
	 * @return array<int, WeightedRandomIntegerSelector>
	 */
	public function getReplacementMap() : array{
		return $this->replacement_map;
	}

	public function getName() : string{
		return "replacesetrandom";
	}

	public function run() : Generator{
		$cursor = new SubChunkIteratorCursor($this->world);
		foreach(EditorTaskUtils::iterateBlocks($this->selection, $cursor) as $operation){
			if($operation === EditorTaskUtils::OP_WRITE_WORLD){
				$cursor->world->setChunk($cursor->chunkX, $cursor->chunkZ, $cursor->chunk);
				continue;
			}

			assert($operation === EditorTaskUtils::OP_WRITE_BUFFER);
			if(!isset($this->replacement_map[$find = $cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z)])){
				continue;
			}

			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $this->replacement_map[$find]->generate(1)->current());
			$tile = $cursor->chunk->getTile($cursor->x, ($cursor->subChunkY << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
			if($tile !== null){
				$cursor->chunk->removeTile($tile);
				// $tile->onBlockDestroyed();
			}
			yield null => Traverser::VALUE;
		}
	}
}