<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use cosmicpe\worldbuilder\Loader;
use pocketmine\Server;
use function assert;
use function chr;
use function file_get_contents;
use function json_decode;
use const JSON_THROW_ON_ERROR;

final class PcPEBlockMapping{

	/**
	 * @return array<int, int>
	 */
	public static function getFullIdMapping() : array{
		static $cached = null;
		if($cached === null){
			$plugin = Server::getInstance()->getPluginManager()->getPlugin("WorldBuilder");
			assert($plugin instanceof Loader);
			$_cached = json_decode(file_get_contents($plugin->getResourcePath("pc_pe_fullid_mapping.json")), true, 512, JSON_THROW_ON_ERROR);
			$cached = [];
			foreach($_cached as $k => $v){
				$cached[(int) $k] = (int) $v;
			}
		}
		return $cached;
	}

	/**
	 * @param array<int, int> $full_id_mapping
	 * @param int $id
	 * @param int $meta
	 * @param int<0, 15> $y
	 * @return array{int, int}|null
	 */
	public static function translate(array $full_id_mapping, int $id, int $meta, int $y) : ?array{
		if(($y & 1) === 0){
			$meta = ($meta & 0x0f);
		}else{
			$meta = ($meta >> 4);
		}
		$value = $full_id_mapping[($id << 4) | ($meta & 0x0f)] ?? null;
		if($value === null){
			return null;
		}
		return [$value >> 4, $value & 0x0f];
	}
}