<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\executor;

use pocketmine\world\World;

final class RegenerateChunksEditorTaskInfo implements EditorTaskInfo{

	public function __construct(
		readonly public World $world,
		readonly public int $x1,
		readonly public int $z1,
		readonly public int $x2,
		readonly public int $z2
	){}
}