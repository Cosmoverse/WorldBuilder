<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy;

use cosmicpe\worldbuilder\editor\task\AdvancedEditorTask;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\session\clipboard\Clipboard;
use cosmicpe\worldbuilder\session\clipboard\ClipboardEntry;
use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class CopyEditorTask extends AdvancedEditorTask{

	/** @var Clipboard */
	private $clipboard;

	/** @var Vector3 */
	private $minimum;

	public function __construct(World $world, Selection $selection, Clipboard $clipboard){
		parent::__construct($world, $selection, (int) Vector3Utils::calculateVolume($selection->getPoint(0), $selection->getPoint(1)));
		$this->clipboard = $clipboard;
		$this->minimum = Vector3::minComponents(...$selection->getPoints());
	}

	public function getClipboard() : Clipboard{
		return $this->clipboard;
	}

	public function getName() : string{
		return "copy";
	}

	protected function onIterate(SubChunkIteratorCursor $cursor) : bool{
		$tile = $cursor->chunk->getTile($cursor->x, $y = ($cursor->subChunkY << 4) + $cursor->y, $cursor->z);
		$this->clipboard->copy(
			($cursor->chunkX << 4) + $cursor->x - $this->minimum->x,
			$y - $this->minimum->y,
			($cursor->chunkZ << 4) + $cursor->z - $this->minimum->z,
			new ClipboardEntry(
				$cursor->sub_chunk->getFullBlock($cursor->x, $cursor->y, $cursor->z),
				$tile !== null ? NamedtagCopierManager::copy($tile) : null
			)
		);
		return false;
	}
}