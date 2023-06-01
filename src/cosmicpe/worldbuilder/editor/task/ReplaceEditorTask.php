<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class ReplaceEditorTask extends AdvancedEditorTask{

	/** @var array<int, int> */
	private array $replacement_map;

	public function __construct(World $world, Selection $selection, BlockToBlockReplacementMap $replacement_map){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->replacement_map = $replacement_map->toFullIdArray();
	}

	/**
	 * @return array<int, int>
	 */
	public function getReplacementMap() : array{
		return $this->replacement_map;
	}

	public function getName() : string{
		return "replace";
	}

	protected function onIterate(SubChunkIteratorCursor $cursor) : bool{
		if(isset($this->replacement_map[$find = $cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z)])){
			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $this->replacement_map[$find]);
			$tile = $cursor->chunk->getTile($cursor->x, ($cursor->subChunkY << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
			if($tile !== null){
				$cursor->chunk->removeTile($tile);
				// $tile->onBlockDestroyed();
			}
			return true;
		}
		return false;
	}
}