<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format\mcschematic;

use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;
use cosmicpe\worldbuilder\editor\utils\clipboard\ClipboardEntry;
use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\math\Vector3;

final class LazyLoadedMinecraftSchematic implements Clipboard{

	public function __construct(
		readonly private Clipboard $inner,
		readonly private MinecraftSchematicExplorer $explorer
	){}

	public function getWidth() : int{
		return 1 + $this->explorer->width;
	}

	public function getLength() : int{
		return 1 + $this->explorer->length;
	}

	public function getHeight() : int{
		return 1 + $this->explorer->height;
	}

	public function getVolume() : int{
		return $this->getWidth() * $this->getLength() * $this->getHeight();
	}

	public function get(int $x, int $y, int $z) : ?ClipboardEntry{
		$result = $this->inner->get($x, $y, $z);
		if($result === null){
			$result = $this->explorer->getSchematicEntryAt($x, $y, $z);
			if($result !== null){
				$this->copy($x, $y, $z, $result);
			}
		}
		return $result;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Generator<ClipboardEntry>
	 */
	public function getAll(&$x, &$y, &$z) : Generator{
		$width = $this->explorer->width;
		$height = $this->explorer->height;
		$length = $this->explorer->length;
		for($i = 0; $i < $width; ++$i){
			for($k = 0; $k < $length; ++$k){
				for($j = 0; $j < $height; ++$j){
					$entry = $this->get($i, $j, $k);
					if($entry !== null){
						$x = $i;
						$y = $j;
						$z = $k;
						yield $entry;
					}
				}
			}
		}
	}

	public function asSelection(Vector3 $relative_to) : Selection{
		return $this->inner->asSelection($relative_to);
	}

	public function getRelativePosition() : Vector3{
		return $this->inner->getRelativePosition();
	}

	public function copy(int $x, int $y, int $z, ClipboardEntry $entry) : void{
		$this->inner->copy($x, $y, $z, $entry);
	}
}