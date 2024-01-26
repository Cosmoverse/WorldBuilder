<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\format\mcschematic;

use cosmicpe\worldbuilder\editor\utils\clipboard\ClipboardEntry;
use cosmicpe\worldbuilder\editor\utils\clipboard\SimpleClipboard;
use Generator;
use pocketmine\math\Vector3;

class LazyLoadedMinecraftSchematic extends SimpleClipboard{

	readonly private MinecraftSchematicExplorer $explorer;

	public function __construct(Vector3 $relative_position, Vector3 $minimum, Vector3 $maximum, MinecraftSchematicExplorer $explorer){
		parent::__construct($relative_position, $minimum, $maximum);
		$this->explorer = $explorer;
	}

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
		$result = parent::get($x, $y, $z);
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
}