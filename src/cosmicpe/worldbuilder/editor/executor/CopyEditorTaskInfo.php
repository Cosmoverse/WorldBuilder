<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\executor;

use cosmicpe\worldbuilder\editor\utils\clipboard\Clipboard;
use pocketmine\world\World;

final class CopyEditorTaskInfo implements EditorTaskInfo{

	public function __construct(
		readonly public World $world,
		readonly public int $x1,
		readonly public int $y1,
		readonly public int $z1,
		readonly public int $x2,
		readonly public int $y2,
		readonly public int $z2,
		readonly public Clipboard $clipboard,
		readonly public bool $generate_new_chunks
	){}

	public function getName() : string{
		return "Copy";
	}
}