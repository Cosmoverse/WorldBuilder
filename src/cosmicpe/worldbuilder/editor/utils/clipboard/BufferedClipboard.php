<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\clipboard;

use Generator;
use pocketmine\math\Vector3;
use pocketmine\utils\Binary;
use function feof;
use function fread;
use function fseek;
use function fwrite;
use function strlen;
use function substr;

final class BufferedClipboard implements Clipboard{
	use SimpleClipboardTrait{ __construct as __parentConstruct; }

	/** @var resource */
	private mixed $resource;

	/** @var array<int, ClipboardEntry> */
	private array $entries = [];

	public function __construct(Vector3 $relative_position, Vector3 $minimum, Vector3 $maximum, mixed $resource){
		$this->__parentConstruct($relative_position, $minimum, $maximum);
		$this->resource = $resource;
	}

	public function get(int $x, int $y, int $z) : ?ClipboardEntry{
		fseek($this->resource, 0);
		while(!feof($this->resource)){
			// 4 bytes - x
			// 2 bytes - y
			// 4 bytes - z
			// 4 bytes - clipboard entry length
			$data = fread($this->resource, 14);
			$entry_length = Binary::readInt(substr($data, 10, 4));
			$clipboard_data = fread($this->resource, $entry_length);

			$px = Binary::readInt(substr($data, 0, 4));
			$py = Binary::readInt(substr($data, 4, 2));
			$pz = Binary::readInt(substr($data, 6, 4));
			if($px === $x && $py === $y && $pz === $z){
				return ClipboardEntry::fromRuntimeData($clipboard_data);
			}
		}
		return null;
	}

	public function copy(int $x, int $y, int $z, ClipboardEntry $entry) : void{
		$entry_data = $entry->toRuntimeData();
		fwrite($this->resource, Binary::writeInt($x) . Binary::writeShort($y) . Binary::writeInt($z) . Binary::writeInt(strlen($entry_data)) . $entry_data);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Generator<ClipboardEntry>
	 */
	public function getAll(&$x, &$y, &$z) : Generator{
		fseek($this->resource, 0);
		while(($data = fread($this->resource, 14)) !== false && $data !== ""){
			// 4 bytes - x
			// 2 bytes - y
			// 4 bytes - z
			// 4 bytes - clipboard entry length
			$x = Binary::readInt(substr($data, 0, 4));
			$y = Binary::readShort(substr($data, 4, 2));
			$z = Binary::readInt(substr($data, 6, 4));

			$entry_length = Binary::readInt(substr($data, 10, 4));
			$data = fread($this->resource, $entry_length);
			yield ClipboardEntry::fromRuntimeData($data);
		}
	}

	public function getVolume() : int{
		return count($this->entries);
	}
}