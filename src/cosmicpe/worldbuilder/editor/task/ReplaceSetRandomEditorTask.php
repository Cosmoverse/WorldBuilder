<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
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

	public function __construct(World $world, Selection $selection, BlockToWeightedRandomSelectorReplacementMap $replacement_map, bool $generate_new_chunks){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)), $generate_new_chunks);
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
		$traverser = new Traverser(EditorTaskUtils::iterateBlocks($this->world, $this->selection, $this->generate_new_chunks));
		while(yield from $traverser->next($operation)){
			[$op, $cursor] = $operation;
			if($op === EditorTaskUtils::OP_WRITE_WORLD){
				assert($cursor instanceof ChunkIteratorCursor);
				$this->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
				continue;
			}

			assert($op === EditorTaskUtils::OP_WRITE_BUFFER);
			assert($cursor instanceof SubChunkIteratorCursor);
			$find = $cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z);
			if(!isset($this->replacement_map[$find])){
				continue;
			}

			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $this->replacement_map[$find]->generate(1)->current());
			$tile = $cursor->chunk->getTile($cursor->x, ($cursor->sub_chunk_y << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
			if($tile !== null){
				$cursor->chunk->removeTile($tile);
				// $tile->onBlockDestroyed();
			}
			yield null => Traverser::VALUE;
		}
	}
}