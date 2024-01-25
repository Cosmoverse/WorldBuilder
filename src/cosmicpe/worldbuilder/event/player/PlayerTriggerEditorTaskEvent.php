<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\event\player;

use cosmicpe\worldbuilder\editor\EditorTaskInstance;
use cosmicpe\worldbuilder\event\WorldBuilderEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

final class PlayerTriggerEditorTaskEvent extends WorldBuilderEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		readonly public Player $player,
		readonly public EditorTaskInstance $instance,
		public ?string $message = null
	){}
}