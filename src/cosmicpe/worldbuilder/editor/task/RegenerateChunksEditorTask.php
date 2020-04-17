<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\session\utils\Selection;
use cosmicpe\worldbuilder\utils\Vector3Utils;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\leveldb\LevelDB;
use pocketmine\world\World;
use ReflectionClassConstant;

class RegenerateChunksEditorTask extends AdvancedChunkEditorTask{

	public function __construct(World $world, Selection $selection){
		$p0 = $selection->getPoint(0)->asVector3();
		$p0->y = 0;

		$p1 = $selection->getPoint(1)->asVector3();
		$p1->y = 0;

		parent::__construct($world, $selection, (int) ceil(Vector3Utils::calculateVolume($p0, $p1) / 256));
	}

	public function getName() : string{
		return "regenerate_chunks";
	}

	protected function onIterate(int $x, int $z) : bool{
		$world = $this->getWorld();
		$world->setChunk($x, $z, new Chunk($x, $z));
		$provider = $world->getProvider();
		if(!($provider instanceof LevelDB)){
			throw new UnsupportedWorldFormatException("Regeneration of chunks is only supported for LevelDb worlds");
		}

		static $tag_version = null;
		if($tag_version === null){
			$const = new ReflectionClassConstant($provider, "TAG_VERSION");
			$tag_version = $const->getValue();
		}
		$provider->getDatabase()->delete(LevelDB::chunkIndex($x, $z) . $tag_version);
		return false;
	}

	public function onCompletion() : void{
		parent::onCompletion();
		$world = $this->getWorld();
		$world->saveChunks();
	}
}