<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\EditorTaskUtils;
use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use Generator;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Traverser;
use function assert;

class SetEditorTask extends EditorTask{

	readonly private int $full_block;

	public function __construct(World $world, Selection $selection, Block $block){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->full_block = $block->getStateId();
	}

	final public function getBlockSet() : Block{
		return RuntimeBlockStateRegistry::getInstance()->fromStateId($this->full_block);
	}

	public function getName() : string{
		return "set";
	}

	public function run() : Generator{
		$cursor = new SubChunkIteratorCursor($this->world);
		foreach(EditorTaskUtils::iterateBlocks($this->selection, $cursor) as $operation){
			if($operation === EditorTaskUtils::OP_WRITE_WORLD){
				$cursor->world->setChunk($cursor->chunkX, $cursor->chunkZ, $cursor->chunk);
				continue;
			}

			assert($operation === EditorTaskUtils::OP_WRITE_BUFFER);
			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $this->full_block);
			$tile = $cursor->chunk->getTile($cursor->x, ($cursor->subChunkY << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
			if($tile !== null){
				$cursor->chunk->removeTile($tile);
				// $tile->onBlockDestroyed();
			}
			yield null => Traverser::VALUE;
		}
	}
}