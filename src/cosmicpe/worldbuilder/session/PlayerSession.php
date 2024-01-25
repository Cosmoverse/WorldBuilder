<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session;

use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\editor\EditorTaskInstance;
use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use cosmicpe\worldbuilder\event\player\PlayerTriggerEditorTaskEvent;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\player\Player;

final class PlayerSession{

	public ?Selection $selection = null;
	public ?Schematic $clipboard_schematic = null;

	public function __construct(
		readonly private Player $player,
		readonly private EditorManager $editor_manager
	){}

	public function pushEditorTask(EditorTaskInstance $instance, ?string $message = null) : bool{
		$ev = new PlayerTriggerEditorTaskEvent($this->player, $instance, $message);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->editor_manager->push($instance);
			$message = $ev->message;
			if($message !== null){
				$this->player->sendMessage($message);
			}
			return true;
		}
		return false;
	}
}