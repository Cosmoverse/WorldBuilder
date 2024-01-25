<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\executor;

use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use pocketmine\world\World;

final class SetSchematicEditorTaskInfo implements EditorTaskInfo{

	public function __construct(
		readonly public World $world,
		readonly public Schematic $clipboard,
		readonly public int $relative_x,
		readonly public int $relative_y,
		readonly public int $relative_z,
		readonly public bool $generate_new_chunks
	){}
}