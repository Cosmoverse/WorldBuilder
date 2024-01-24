<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\event\player;

use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\event\WorldBuilderEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class PlayerTriggerEditorTaskEvent extends WorldBuilderEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		readonly public Player $player,
		readonly public EditorTask $task,
		public ?string $message = null
	){}
}