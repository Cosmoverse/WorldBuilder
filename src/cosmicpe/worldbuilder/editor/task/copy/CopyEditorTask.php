<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy;

use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\editor\task\utils\EditorTaskUtils;
use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use cosmicpe\worldbuilder\editor\utils\schematic\SchematicEntry;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use Generator;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Traverser;

class CopyEditorTask extends EditorTask{

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

	public function run() : Generator{
		$cursor = new SubChunkIteratorCursor($this->world);
		foreach(EditorTaskUtils::iterateBlocks($this->selection, $cursor) as $operation){
			if($operation !== EditorTaskUtils::OP_WRITE_BUFFER){
				continue;
			}
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
			yield null => Traverser::VALUE;
		}
	}
}