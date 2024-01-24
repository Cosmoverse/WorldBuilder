<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class SetEditorTask extends AdvancedEditorTask{

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

	protected function onIterate(SubChunkIteratorCursor $cursor) : bool{
		$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $this->full_block);
		$tile = $cursor->chunk->getTile($cursor->x, ($cursor->subChunkY << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
		if($tile !== null){
			$cursor->chunk->removeTile($tile);
			// $tile->onBlockDestroyed();
		}
		return true;
	}
}