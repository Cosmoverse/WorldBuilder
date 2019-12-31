<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session;

use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\event\player\PlayerTriggerEditorTaskEvent;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\player\Player;

final class PlayerSession{

	/** @var Selection|null */
	private $selection;

	/** @var Player */
	private $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function getSelection() : ?Selection{
		return $this->selection;
	}

	public function setSelection(?Selection $selection) : void{
		$this->selection = $selection;
	}

	public function pushEditorTask(EditorTask $task, ?string $message = null) : bool{
		$ev = new PlayerTriggerEditorTaskEvent($this->player, $task, $message);
		$ev->call();
		if(!$ev->isCancelled()){
			EditorManager::push($task);
			$message = $ev->getMessage();
			if($message !== null){
				$this->player->sendMessage($message);
			}
			return true;
		}

		return false;
	}
}