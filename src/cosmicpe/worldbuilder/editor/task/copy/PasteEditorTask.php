<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy;

use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\session\clipboard\Clipboard;
use Ds\Set;
use Generator;
use pocketmine\block\tile\TileFactory;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class PasteEditorTask extends EditorTask{

	/** @var Vector3 */
	private $relative_position;

	/** @var Clipboard */
	private $clipboard;

	public function __construct(World $world, Clipboard $clipboard, Vector3 $relative_position){
		$this->relative_position = $relative_position->floor()->add($clipboard->getRelativePosition());
		parent::__construct($world, $clipboard->asSelection($this->relative_position), $clipboard->getVolume());
		$this->clipboard = $clipboard;
	}

	public function getName() : string{
		return "paste";
	}

	public function run() : Generator{
		$relative_pos = $this->relative_position;
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
				$tiles[] = TileFactory::createFromData($world, NamedtagCopierManager::moveTo($entry->tile_nbt, $x, $y, $z));
			}

			$this->iterator->currentSubChunk->setFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f, $entry->full_block);
			$chunks->add(World::chunkHash($x >> 4, $z >> 4));
			yield true;
		}

		foreach($chunks as $hash){
			World::getXZ($hash, $chunkX, $chunkZ);
			$this->onChunkChanged($chunkX, $chunkZ);
		}

		// Send tiles AFTER blocks have been placed, or else chests don't show up paired
		foreach($tiles as $tile){
			$world->addTile($tile);
			yield true;
		}
	}
}