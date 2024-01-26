<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\utils\clipboard;

use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use function substr;

final class ClipboardEntry{

	public static function fromRuntimeData(string $data) : self{
		static $serializer = new BigEndianNbtSerializer();
		$block_state_id = Binary::readLong(substr($data, 0, 8));
		if($data[8] === "\x01"){
			$tile_nbt = $serializer->read(substr($data, 9))->mustGetCompoundTag();
		}else{
			$tile_nbt = null;
		}
		return new self($block_state_id, $tile_nbt);
	}

	public function __construct(
		readonly public int $block_state_id,
		readonly public ?CompoundTag $tile_nbt
	){}

	public function toRuntimeData() : string{
		static $serializer = new BigEndianNbtSerializer();
		if($this->tile_nbt !== null){
			$tile_data = "\x01" . $serializer->write(new TreeRoot($this->tile_nbt));
		}else{
			$tile_data = "\x00";
		}
		return Binary::writeLong($this->block_state_id) . $tile_data;
	}
}