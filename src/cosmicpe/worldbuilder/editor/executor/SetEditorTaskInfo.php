<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\executor;

use pocketmine\block\Block;
use pocketmine\world\World;

final class SetEditorTaskInfo implements EditorTaskInfo{

	public function __construct(
		readonly public World $world,
		readonly public int $x1,
		readonly public int $y1,
		readonly public int $z1,
		readonly public int $x2,
		readonly public int $y2,
		readonly public int $z2,
		readonly public Block $block,
		readonly public bool $generate_new_chunks
	){}
}