<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\session\clipboard\Clipboard;
use Ds\Set;
use Generator;
use pocketmine\block\tile\TileFactory;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class PasteEditorTask extends EditorTask{

	/** @var Clipboard */
	private $clipboard;

	/** @var Vector3 */
	private $relative_position;

	public function __construct(World $world, Clipboard $clipboard, Vector3 $relative_position){
		parent::__construct($world, $clipboard->getSelection(), $clipboard->getVolume());
		$this->clipboard = $clipboard;
		$this->relative_position = $relative_position;
	}

	public function getName() : string{
		return "paste";
	}

	public function run() : Generator{
		$relative_pos = $this->relative_position->floor()->add($this->clipboard->getRelativePosition());
		$world = $this->getWorld();
		$chunks = new Set();
		$tiles = [];
		foreach($this->clipboard->getAll($x, $y, $z) as $entry){
			$x += $relative_pos->x;
			$y += $relative_pos->y;
			$z += $relative_pos->z;
			if(!$this->iterator->moveTo($x, $y, $z, true)){
				continue;
			}

			if($entry->tile_nbt !== null){
				$tiles[] = TileFactory::createFromData($world, $entry->tile_nbt);
			}

			$this->iterator->currentSubChunk->setFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $entry->full_block);
			$chunks->add(World::chunkHash($x >> 4, $z >> 4));
			yield true;
		}

		foreach($chunks as $hash){
			World::getXZ($hash, $chunkX, $chunkZ);
			$this->onChunkChanged($chunkX, $chunkZ, [Chunk::DIRTY_FLAG_TERRAIN, Chunk::DIRTY_FLAG_TILES]);
		}

		foreach($tiles as $tile){
			$world->addTile($tile);
			yield true;
		}
	}
}