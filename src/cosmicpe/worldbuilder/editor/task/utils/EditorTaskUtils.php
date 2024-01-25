<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\utils;

use Closure;
use Generator;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitGenerator\Traverser;
use function max;
use function min;

final class EditorTaskUtils{

	public const OP_WRITE_BUFFER = 0;
	public const OP_WRITE_WORLD = 1;

	/**
	 * @param World $world
	 * @param int $x
	 * @param int $z
	 * @param bool $generate
	 * @return Generator<mixed, Await::RESOLVE, void, Chunk|null>
	 */
	private static function retrieveChunk(World $world, int $x, int $z, bool $generate) : Generator{
		if(!$generate){
			return $world->loadChunk($x, $z);
		}
		/** @var Closure(Closure(Chunk|null) : void) : void $closure */
		$closure = static function(Closure $resolve) use($x, $z, $world) : void{
			$world->orderChunkPopulation($x, $z, EditorChunkLoader::instance())->onCompletion($resolve, function() use($resolve) : void{ $resolve(null); });
		};
		return yield from Await::promise($closure);
	}

	/**
	 * @param World $world
	 * @param int $x1
	 * @param int $z1
	 * @param int $x2
	 * @param int $z2
	 * @param bool $generate
	 * @return Generator<ChunkIteratorCursor, Traverser::VALUE|Await::RESOLVE>
	 */
	public static function iterateChunks(World $world, int $x1, int $z1, int $x2, int $z2, bool $generate) : Generator{
		$min_x = min($x1, $x2) >> Chunk::COORD_BIT_SIZE;
		$min_z = min($z1, $z2) >> Chunk::COORD_BIT_SIZE;
		$max_x = max($x1, $x2) >> Chunk::COORD_BIT_SIZE;
		$max_z = max($z1, $z2) >> Chunk::COORD_BIT_SIZE;
		for($chunkX = $min_x; $chunkX <= $max_x; ++$chunkX){
			for($chunkZ = $min_z; $chunkZ <= $max_z; ++$chunkZ){
				$chunk = yield from self::retrieveChunk($world, $chunkX, $chunkZ, $generate);
				if($chunk === null){
					continue;
				}
				yield new ChunkIteratorCursor($chunkX, $chunkZ, $chunk) => Traverser::VALUE;
				if($generate){
					$world->unregisterChunkLoader(EditorChunkLoader::instance(), $chunkX, $chunkZ);
				}
			}
		}
	}

	/**
	 * @param World $world
	 * @param int $x1
	 * @param int $y1
	 * @param int $z1
	 * @param int $x2
	 * @param int $y2
	 * @param int $z2
	 * @param bool $generate
	 * @return Generator<array{self::OP_WRITE_BUFFER, SubChunkIteratorCursor}|array{self::OP_WRITE_WORLD, ChunkIteratorCursor}, Traverser::VALUE|Await::RESOLVE>
	 */
	public static function iterateBlocks(World $world, int $x1, int $y1, int $z1, int $x2, int $y2, int $z2, bool $generate) : Generator{
		$min_x = min($x1, $x2);
		$min_y = min($y1, $y2);
		$min_z = min($z1, $z2);
		$max_x = max($x1, $x2);
		$max_y = max($y1, $y2);
		$max_z = max($z1, $z2);

		$min_chunkX = $min_x >> Chunk::COORD_BIT_SIZE;
		$max_chunkX = $max_x >> Chunk::COORD_BIT_SIZE;
		$min_subChunkY = $min_y >> Chunk::COORD_BIT_SIZE;
		$max_subChunkY = $max_y >> Chunk::COORD_BIT_SIZE;
		$min_chunkZ = $min_z >> Chunk::COORD_BIT_SIZE;
		$max_chunkZ = $max_z >> Chunk::COORD_BIT_SIZE;

		for($chunkX = $min_chunkX; $chunkX <= $max_chunkX; ++$chunkX){
			$abs_cx = $chunkX << Chunk::COORD_BIT_SIZE;
			$min_i = max($abs_cx, $min_x) & Chunk::COORD_MASK;
			$max_i = min($abs_cx + Chunk::COORD_MASK, $max_x) & Chunk::COORD_MASK;
			for($chunkZ = $min_chunkZ; $chunkZ <= $max_chunkZ; ++$chunkZ){
				$chunk = yield from self::retrieveChunk($world, $chunkX, $chunkZ, $generate);
				if($chunk === null){
					continue;
				}

				$abs_cz = $chunkZ << Chunk::COORD_BIT_SIZE;
				$min_k = max($abs_cz, $min_z) & Chunk::COORD_MASK;
				$max_k = min($abs_cz + Chunk::COORD_MASK, $max_z) & Chunk::COORD_MASK;

				for($subChunkY = $min_subChunkY; $subChunkY <= $max_subChunkY; ++$subChunkY){
					$sub_chunk = $chunk->getSubChunk($subChunkY);

					$abs_cy = $subChunkY << Chunk::COORD_BIT_SIZE;
					$min_j = max($abs_cy, $min_y) & Chunk::COORD_MASK;
					$max_j = min($abs_cy + Chunk::COORD_MASK, $max_y) & Chunk::COORD_MASK;
					for($y = $min_j; $y <= $max_j; ++$y){
						for($x = $min_i; $x <= $max_i; ++$x){
							for($z = $min_k; $z <= $max_k; ++$z){
								yield [self::OP_WRITE_BUFFER, new SubChunkIteratorCursor($x, $y, $z, $chunkX, $chunkZ, $subChunkY, $sub_chunk, $chunk)] => Traverser::VALUE;
							}
						}
					}
				}
				yield [self::OP_WRITE_WORLD, new ChunkIteratorCursor($chunkX, $chunkZ, $chunk)] => Traverser::VALUE;
				if($generate){
					$world->unregisterChunkLoader(EditorChunkLoader::instance(), $chunkX, $chunkZ);
				}
			}
		}
	}
}