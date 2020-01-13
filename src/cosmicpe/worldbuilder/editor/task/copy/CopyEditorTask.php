<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy;

use cosmicpe\worldbuilder\editor\task\AdvancedEditorTask;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
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

	protected function onIterate(int $x, int $y, int $z) : bool{
		$tile = $this->iterator->currentChunk->getTile($x, $y, $z);
		$this->clipboard->copy(
			($this->iterator->currentChunk->getX() << 4) + $x - $this->minimum->x,
			$y - $this->minimum->y,
			($this->iterator->currentChunk->getZ() << 4) + $z - $this->minimum->z,
			new ClipboardEntry(
				$this->iterator->currentSubChunk->getFullBlock($x, $y & 0x0f, $z),
				$tile !== null ? NamedtagCopierManager::copy($tile) : null
			)
		);
		return false;
	}
}