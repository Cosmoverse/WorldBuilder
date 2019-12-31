<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\event\player;

use cosmicpe\buildertools\editor\task\EditorTask;
use cosmicpe\buildertools\event\BuilderToolsEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class PlayerTriggerEditorTaskEvent extends BuilderToolsEvent implements Cancellable{
	use CancellableTrait;

	/** @var Player */
	private $player;

	/** @var EditorTask */
	private $task;

	/** @var string|null */
	private $message;

	public function __construct(Player $player, EditorTask $task, ?string $message = null){
		$this->player = $player;
		$this->task = $task;
		$this->message = $message;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getTask() : EditorTask{
		return $this->task;
	}

	public function getMessage() : ?string{
		return $this->message;
	}

	public function setMessage(?string $message) : void{
		$this->message = $message;
	}
}