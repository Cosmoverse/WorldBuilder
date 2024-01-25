<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use Generator;
use pocketmine\block\tile\TileFactory;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\utils\SubChunkExplorerStatus;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Traverser;

abstract class SetSchematicEditorTask extends EditorTask{

	readonly private Vector3 $relative_position;
	readonly private Schematic $clipboard;

	public function __construct(World $world, Schematic $clipboard, Vector3 $relative_position, bool $generate_new_chunks){
		$this->relative_position = $relative_position->floor()->addVector($clipboard->getRelativePosition());
		parent::__construct($world, $clipboard->asSelection($this->relative_position), $clipboard->getVolume(), $generate_new_chunks);
		$this->clipboard = $clipboard;
	}

	public function run() : Generator{
		$relative_pos = $this->relative_position->floor();
		$world = $this->world;
		$chunks = [];
		$tiles = [];
		$tile_factory = TileFactory::getInstance();

		$iterator = new SubChunkExplorer($world);
		foreach($this->clipboard->getAll($x, $y, $z) as $entry){
			$x += $relative_pos->x;
			$y += $relative_pos->y;
			$z += $relative_pos->z;
			if($iterator->moveTo($x, $y, $z) === SubChunkExplorerStatus::INVALID){
				continue;
			}

			if($entry->tile_nbt !== null){
				$tiles[] = $tile_factory->createFromData($world, NamedtagCopierManager::moveTo($entry->tile_nbt, $x, $y, $z));
			}

			$iterator->currentSubChunk->setBlockStateId($x & Chunk::COORD_MASK, $y & Chunk::COORD_MASK, $z & Chunk::COORD_MASK, $entry->block_state_id);
			$chunks[World::chunkHash($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)] = true;
			yield null => Traverser::VALUE;
		}

		foreach($chunks as $hash => $_){
			World::getXZ($hash, $chunkX, $chunkZ);
			$chunk = $world->loadChunk($chunkX, $chunkZ);
			if($chunk !== null){
				$this->world->setChunk($chunkX, $chunkZ, $chunk);
			}
		}

		// Send tiles AFTER blocks have been placed, or else chests don't show up paired
		foreach($tiles as $tile){
			$pos = $tile->getPosition();
			$old_tile = $world->getTileAt($pos->x, $pos->y, $pos->z);
			if($old_tile !== null){
				$world->removeTile($old_tile);
			}
			$world->addTile($tile);
			yield null => Traverser::VALUE;
		}
	}
}