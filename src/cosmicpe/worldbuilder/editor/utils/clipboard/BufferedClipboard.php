<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\clipboard;

use Generator;
use pocketmine\math\Vector3;
use pocketmine\utils\Binary;
use function fread;
use function fseek;
use function fwrite;
use function strlen;
use function substr;
use const SEEK_CUR;

final class BufferedClipboard implements Clipboard{
	use SimpleClipboardTrait{ __construct as __parentConstruct; }

	/** @var resource */
	private mixed $resource;

	public function __construct(Vector3 $relative_position, Vector3 $minimum, Vector3 $maximum, mixed $resource){
		$this->__parentConstruct($relative_position, $minimum, $maximum);
		$this->resource = $resource;
	}

	public function get(int $x, int $y, int $z) : ?ClipboardEntry{
		fseek($this->resource, 0);
		while(($data = fread($this->resource, 4)) !== false && $data !== ""){
			$data = fread($this->resource, Binary::readInt($data));
			$px = Binary::readInt(substr($data, 0, 4));
			$py = Binary::readShort(substr($data, 4, 2));
			$pz = Binary::readInt(substr($data, 6, 4));
			if($px === $x && $py === $y && $pz === $z){
				return ClipboardEntry::fromRuntimeData(substr($data, 10));
			}
		}
		return null;
	}

	public function copy(int $x, int $y, int $z, ClipboardEntry $entry) : void{
		$data = Binary::writeInt($x) . Binary::writeShort($y) . Binary::writeInt($z) . $entry->toRuntimeData();
		fwrite($this->resource, Binary::writeInt(strlen($data)) . $data);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Generator<ClipboardEntry>
	 */
	public function getAll(&$x, &$y, &$z) : Generator{
		fseek($this->resource, 0);
		while(($data = fread($this->resource, 4)) !== false && $data !== ""){
			$data = fread($this->resource, Binary::readInt($data));
			$x = Binary::readInt(substr($data, 0, 4));
			$y = Binary::readShort(substr($data, 4, 2));
			$z = Binary::readInt(substr($data, 6, 4));
			yield ClipboardEntry::fromRuntimeData(substr($data, 10));
		}
	}

	public function calculateEntryCount() : int{
		$count = 0;
		fseek($this->resource, 0);
		while(($data = fread($this->resource, 4)) !== false && $data !== ""){
			fseek($this->resource, Binary::readInt($data), SEEK_CUR);
			++$count;
		}
		return $count;
	}
}