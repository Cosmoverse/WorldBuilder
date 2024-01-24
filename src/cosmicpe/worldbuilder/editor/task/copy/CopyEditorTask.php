<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy;

use cosmicpe\worldbuilder\editor\task\AdvancedEditorTask;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use cosmicpe\worldbuilder\editor\utils\schematic\SchematicEntry;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class CopyEditorTask extends AdvancedEditorTask{

	readonly public Schematic $clipboard;
	readonly private Vector3 $minimum;

	public function __construct(World $world, Selection $selection, Schematic $clipboard){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->clipboard = $clipboard;
		$this->minimum = Vector3::minComponents(...$selection->getPoints());
	}

	public function getName() : string{
		return "copy";
	}

	protected function onIterate(SubChunkIteratorCursor $cursor) : bool{
		$tile = $cursor->chunk->getTile($cursor->x, $y = ($cursor->subChunkY << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
		$this->clipboard->copy(
			($cursor->chunkX << Chunk::COORD_BIT_SIZE) + $cursor->x - $this->minimum->x,
			$y - $this->minimum->y,
			($cursor->chunkZ << Chunk::COORD_BIT_SIZE) + $cursor->z - $this->minimum->z,
			new SchematicEntry(
				$cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z),
				$tile !== null ? NamedtagCopierManager::copy($tile) : null
			)
		);
		return false;
	}
}