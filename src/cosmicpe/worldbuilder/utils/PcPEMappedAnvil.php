<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use cosmicpe\worldbuilder\Loader;
use pocketmine\Server;
use pocketmine\world\format\io\region\Anvil;
use pocketmine\world\format\PalettedBlockArray;
use function assert;
use function chr;
use function file_get_contents;
use function json_decode;
use function ord;
use const JSON_THROW_ON_ERROR;

/**
 * An implementation of Anvil class that handles PC-PE block conversion
 */
final class PcPEMappedAnvil extends Anvil{

	/**
	 * @return array<int, int>
	 */
	public static function getPcPeFullIdMapping() : array{
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

	public function __construct(string $path, \Logger $logger){
		parent::__construct($path, $logger);
	}

	protected function palettizeLegacySubChunkYZX(string $idArray, string $metaArray, \Logger $logger) : PalettedBlockArray{
		[$idArray, $metaArray] = $this->translatePcBlocksToPe($idArray, $metaArray);
		return parent::palettizeLegacySubChunkYZX($idArray, $metaArray, $logger);
	}

	/**
	 * @param string $idArray
	 * @param string $metaArray
	 * @return array{string, string}
	 */
	private function translatePcBlocksToPe(string $idArray, string $metaArray) : array{
		$mapping = self::getPcPeFullIdMapping();
		for($y = 0; $y < 16; $y++){
			for($z = 0; $z < 16; $z++){
				for($x = 0; $x < 16; $x++){
					$id_index = ($x << 8) | ($z << 4) | $y;
					$meta_index = ($x << 7) + ($z << 3) + ($y >> 1);
					$id = ord($idArray[$id_index]);
					$meta = ord($metaArray[$meta_index]);
					if(($y & 1) === 0){
						$meta = ($meta & 0x0f);
					}else{
						$meta = ($meta >> 4);
					}
					$full_id = ($id << 4) | ($meta & 0x0f);
					if(isset($mapping[$full_id])){
						$idArray[$id_index] = chr($mapping[$full_id] >> 4);
						$metaArray[$meta_index] = chr($mapping[$full_id] & 0x0f);
					}
				}
			}
		}
		return [$idArray, $metaArray];
	}
}