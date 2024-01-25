<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\executor;

use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use pocketmine\world\World;

final class ReplaceEditorTaskInfo implements EditorTaskInfo{

	public function __construct(
		readonly public World $world,
		readonly public int $x1,
		readonly public int $y1,
		readonly public int $z1,
		readonly public int $x2,
		readonly public int $y2,
		readonly public int $z2,
		readonly public BlockToBlockReplacementMap $replacement_map,
		readonly public bool $generate_new_chunks
	){}
}