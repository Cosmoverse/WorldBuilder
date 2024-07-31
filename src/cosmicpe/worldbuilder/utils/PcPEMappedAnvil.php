<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use pocketmine\world\format\io\region\Anvil;
use pocketmine\world\format\PalettedBlockArray;
use function chr;
use function ord;

/**
 * An implementation of Anvil class that handles PC-PE block conversion
 */
final class PcPEMappedAnvil extends Anvil{

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
		$mapping = PcPEBlockMapping::getFullIdMapping();
		for($y = 0; $y < 16; $y++){
			for($z = 0; $z < 16; $z++){
				for($x = 0; $x < 16; $x++){
					$id_index = ($x << 8) | ($z << 4) | $y;
					$meta_index = ($x << 7) + ($z << 3) + ($y >> 1);
					$id = ord($idArray[$id_index]);
					$meta = ord($metaArray[$meta_index]);
					$translation = PcPEBlockMapping::translate($mapping, $id, $meta, $y);
					if($translation !== null){
						$idArray[$id_index] = chr($translation[0]);
						$metaArray[$meta_index] = chr($translation[1]);
					}
				}
			}
		}
		return [$idArray, $metaArray];
	}
}