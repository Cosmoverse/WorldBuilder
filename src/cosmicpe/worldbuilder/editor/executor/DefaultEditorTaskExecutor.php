<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\executor;

use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\task\utils\EditorTaskUtils;
use cosmicpe\worldbuilder\editor\task\utils\SubChunkIteratorCursor;
use cosmicpe\worldbuilder\editor\utils\schematic\SchematicEntry;
use cosmicpe\worldbuilder\utils\MathUtils;
use Generator;
use pocketmine\block\tile\TileFactory;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\leveldb\LevelDB;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\utils\SubChunkExplorerStatus;
use pocketmine\world\World;
use ReflectionClassConstant;
use SOFe\AwaitGenerator\Traverser;
use function assert;
use function min;

final class DefaultEditorTaskExecutor{

	public function __construct(){
	}

	/**
	 * @param CopyEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function copy(CopyEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2);
		$traverser = new Traverser(EditorTaskUtils::iterateBlocks($info->world, $info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2, $info->generate_new_chunks));
		$min_x = min($info->x1, $info->x2);
		$min_y = min($info->y1, $info->y2);
		$min_z = min($info->z1, $info->z2);
		while(yield from $traverser->next($operation)){
			[$op, $cursor] = $operation;
			if($op !== EditorTaskUtils::OP_WRITE_BUFFER){
				continue;
			}
			assert($cursor instanceof SubChunkIteratorCursor);
			$tile = $cursor->chunk->getTile($cursor->x, $y = ($cursor->sub_chunk_y << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
			$info->clipboard->copy(
				($cursor->chunk_x << Chunk::COORD_BIT_SIZE) + $cursor->x - $min_x,
				$y - $min_y,
				($cursor->chunk_z << Chunk::COORD_BIT_SIZE) + $cursor->z - $min_z,
				new SchematicEntry(
					$cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z),
					$tile !== null ? NamedtagCopierManager::copy($tile) : null
				)
			);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param RegenerateChunksEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function regenerateChunks(RegenerateChunksEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1 >> Chunk::COORD_BIT_SIZE, 0, $info->z1 >> Chunk::COORD_BIT_SIZE, $info->x2 >> Chunk::COORD_BIT_SIZE, 0, $info->z2 >> Chunk::COORD_BIT_SIZE);
		$traverser = new Traverser(EditorTaskUtils::iterateChunks($info->world, $info->x1, $info->z1, $info->x2, $info->z2, false));
		while(yield from $traverser->next($cursor)){
			$info->world->unloadChunk($cursor->x, $cursor->z, false, false);
			$provider = $info->world->getProvider();
			$provider instanceof LevelDB || throw new UnsupportedWorldFormatException("Regeneration of chunks is only supported for LevelDb worlds");

			static $tag_version = null;
			if($tag_version === null){
				$const = new ReflectionClassConstant($provider, "TAG_VERSION");
				$tag_version = $const->getValue();
			}
			$provider->getDatabase()->delete(LevelDB::chunkIndex($cursor->x, $cursor->z) . $tag_version);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param ReplaceEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function replace(ReplaceEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2);
		$replacement_map = $info->replacement_map->toFullIdArray();
		$traverser = new Traverser(EditorTaskUtils::iterateBlocks($info->world, $info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2, $info->generate_new_chunks));
		while(yield from $traverser->next($operation)){
			[$op, $cursor] = $operation;
			if($op === EditorTaskUtils::OP_WRITE_WORLD){
				assert($cursor instanceof ChunkIteratorCursor);
				$info->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
				continue;
			}

			assert($op === EditorTaskUtils::OP_WRITE_BUFFER);
			assert($cursor instanceof SubChunkIteratorCursor);
			$find = $cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z);
			if(!isset($replacement_map[$find])){
				++$progress;
				continue;
			}

			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $replacement_map[$find]);
			$this->destroyTile($cursor);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param ReplaceSetRandomEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function replaceSetRandom(ReplaceSetRandomEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2);
		$replacement_map = $info->replacement_map->toFullIdArray();
		$traverser = new Traverser(EditorTaskUtils::iterateBlocks($info->world, $info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2, $info->generate_new_chunks));
		while(yield from $traverser->next($operation)){
			[$op, $cursor] = $operation;
			if($op === EditorTaskUtils::OP_WRITE_WORLD){
				assert($cursor instanceof ChunkIteratorCursor);
				$info->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
				continue;
			}

			assert($op === EditorTaskUtils::OP_WRITE_BUFFER);
			assert($cursor instanceof SubChunkIteratorCursor);
			$find = $cursor->sub_chunk->getBlockStateId($cursor->x, $cursor->y, $cursor->z);
			if(!isset($replacement_map[$find])){
				++$progress;
				continue;
			}

			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $replacement_map[$find]->generate(1)->current());
			$this->destroyTile($cursor);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param SetEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function set(SetEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2);
		$block = $info->block->getStateId();
		$traverser = new Traverser(EditorTaskUtils::iterateBlocks($info->world, $info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2, $info->generate_new_chunks));
		while(yield from $traverser->next($operation)){
			[$op, $cursor] = $operation;
			if($op === EditorTaskUtils::OP_WRITE_WORLD){
				assert($cursor instanceof ChunkIteratorCursor);
				$info->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
				continue;
			}
			assert($op === EditorTaskUtils::OP_WRITE_BUFFER);
			assert($cursor instanceof SubChunkIteratorCursor);
			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $block);
			$this->destroyTile($cursor);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param SetBiomeEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function setBiome(SetBiomeEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1 >> Chunk::COORD_BIT_SIZE, 0, $info->z1 >> Chunk::COORD_BIT_SIZE, $info->x2 >> Chunk::COORD_BIT_SIZE, 0, $info->z2 >> Chunk::COORD_BIT_SIZE);
		$traverser = new Traverser(EditorTaskUtils::iterateChunks($info->world, $info->x1, $info->z1, $info->x2, $info->z2, $info->generate_new_chunks));
		while(yield from $traverser->next($cursor)){
			assert($cursor instanceof ChunkIteratorCursor);
			foreach($cursor->chunk->getSubChunks() as $y => $sub_chunk){
				$cursor->chunk->setSubChunk($y, new SubChunk(
					$sub_chunk->getEmptyBlockId(),
					$sub_chunk->getBlockLayers(),
					new PalettedBlockArray($info->biome_id),
					$sub_chunk->getBlockSkyLightArray(),
					$sub_chunk->getBlockLightArray()
				));
			}
			$info->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param SetRandomEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function setRandom(SetRandomEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = MathUtils::calculateVolume($info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2);
		$traverser = new Traverser(EditorTaskUtils::iterateBlocks($info->world, $info->x1, $info->y1, $info->z1, $info->x2, $info->y2, $info->z2, $info->generate_new_chunks));
		while(yield from $traverser->next($operation)){
			[$op, $cursor] = $operation;
			if($op === EditorTaskUtils::OP_WRITE_WORLD){
				assert($cursor instanceof ChunkIteratorCursor);
				$info->world->setChunk($cursor->x, $cursor->z, $cursor->chunk);
				continue;
			}
			assert($op === EditorTaskUtils::OP_WRITE_BUFFER);
			assert($cursor instanceof SubChunkIteratorCursor);
			$cursor->sub_chunk->setBlockStateId($cursor->x, $cursor->y, $cursor->z, $info->selector->generate(1)->current());
			$this->destroyTile($cursor);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	/**
	 * @param SetSchematicEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function setSchematic(SetSchematicEditorTaskInfo $info) : Generator{
		yield from $this->paste(new PasteEditorTaskInfo($info->world, $info->clipboard, $info->relative_x, $info->relative_y, $info->relative_z, $info->generate_new_chunks));
	}

	/**
	 * @param PasteEditorTaskInfo $info
	 * @return Generator<array{int, int}, Traverser::VALUE>
	 */
	public function paste(PasteEditorTaskInfo $info) : Generator{
		$progress = 0;
		$total = $info->clipboard->getVolume();

		$clipboard_relative_pos = $info->clipboard->getRelativePosition();
		$relative_x = $info->relative_x + $clipboard_relative_pos->x;
		$relative_y = $info->relative_y + $clipboard_relative_pos->y;
		$relative_z = $info->relative_z + $clipboard_relative_pos->z;

		$chunks = [];
		$tiles = [];
		$tile_factory = TileFactory::getInstance();

		$iterator = new SubChunkExplorer($info->world);
		foreach($info->clipboard->getAll($x, $y, $z) as $entry){
			$x += $relative_x;
			$y += $relative_y;
			$z += $relative_z;
			if($iterator->moveTo($x, $y, $z) === SubChunkExplorerStatus::INVALID){
				++$progress;
				continue;
			}

			if($entry->tile_nbt !== null){
				$tiles[] = $tile_factory->createFromData($info->world, NamedtagCopierManager::moveTo($entry->tile_nbt, $x, $y, $z));
				++$total;
			}

			$iterator->currentSubChunk->setBlockStateId($x & Chunk::COORD_MASK, $y & Chunk::COORD_MASK, $z & Chunk::COORD_MASK, $entry->block_state_id);
			$chunks[World::chunkHash($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)] = true;
			yield [++$progress, $total] => Traverser::VALUE;
		}

		foreach($chunks as $hash => $_){
			World::getXZ($hash, $chunkX, $chunkZ);
			$chunk = $info->world->loadChunk($chunkX, $chunkZ);
			if($chunk !== null){
				$info->world->setChunk($chunkX, $chunkZ, $chunk);
			}
		}

		// Send tiles AFTER blocks have been placed, or else chests don't show up paired
		foreach($tiles as $tile){
			$pos = $tile->getPosition();
			$old_tile = $info->world->getTileAt($pos->x, $pos->y, $pos->z);
			if($old_tile !== null){
				$info->world->removeTile($old_tile);
			}
			$info->world->addTile($tile);
			yield [++$progress, $total] => Traverser::VALUE;
		}
	}

	public function destroyTile(SubChunkIteratorCursor $cursor) : void{
		$tile = $cursor->chunk->getTile($cursor->x, ($cursor->sub_chunk_y << Chunk::COORD_BIT_SIZE) + $cursor->y, $cursor->z);
		if($tile !== null){
			$cursor->chunk->removeTile($tile);
			// $tile->onBlockDestroyed();
		}
	}
}