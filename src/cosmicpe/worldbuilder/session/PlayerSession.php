<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session;

use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\editor\utils\schematic\Schematic;
use cosmicpe\worldbuilder\event\player\PlayerTriggerEditorTaskEvent;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\player\Player;

final class PlayerSession{

	private ?Selection $selection = null;
	private ?Schematic $clipboard_schematic = null;
	private Player $player;
	private EditorManager $editor_manager;

	public function __construct(Player $player, EditorManager $editor_manager){
		$this->player = $player;
		$this->editor_manager = $editor_manager;
	}

	public function getSelection() : ?Selection{
		return $this->selection;
	}

	public function setSelection(?Selection $selection) : void{
		$this->selection = $selection;
	}

	public function getClipboardSchematic() : ?Schematic{
		return $this->clipboard_schematic;
	}

	public function setClipboardSchematic(?Schematic $schematic) : void{
		$this->clipboard_schematic = $schematic;
	}

	public function pushEditorTask(EditorTask $task, ?string $message = null) : bool{
		$ev = new PlayerTriggerEditorTaskEvent($this->player, $task, $message);
		$ev->call();
		if(!$ev->isCancelled()){
			$this->editor_manager->push($task);
			$message = $ev->getMessage();
			if($message !== null){
				$this->player->sendMessage($message);
			}
			return true;
		}

		return false;
	}
}