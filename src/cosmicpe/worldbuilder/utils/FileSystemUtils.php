<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\utils;

use DirectoryIterator;
use Generator;
use SplFileInfo;

final class FileSystemUtils{

	/**
	 * @param string $directory
	 * @param string $extension
	 * @return Generator<SplFileInfo>
	 */
	public static function findFilesWithExtension(string $directory, string $extension) : Generator{
		foreach(new DirectoryIterator($directory) as $item){
			if($item->isFile() && $item->getExtension() === $extension){
				yield $item;
			}
		}
	}

	public static function printBytesToHumanReadable(int $bytes, int $decimals = 2) : string{
		// src: https://stackoverflow.com/questions/15188033/human-readable-file-size

		static $file_sizes = "BKMGTP";
		$bytes_string = (string) $bytes;
		$factor = floor((strlen($bytes_string) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes_string / (1024 ** $factor)) . ($file_sizes[$factor] ?? "");
	}
}