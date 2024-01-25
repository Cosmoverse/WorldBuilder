<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use WeakReference;
use function number_format;

final class PopupProgressEditorTaskListener implements EditorTaskListener{

	/** @var WeakReference<Player> */
	readonly private WeakReference $player;

	public function __construct(Player $player){
		$this->player = WeakReference::create($player);
	}

	public function onRegister(EditorTaskInfo $info) : void{
		$this->player->get()?->sendTip(TextFormat::GREEN . $info->getName() . TextFormat::GRAY . ": " . TextFormat::YELLOW . "Beginning...");
	}

	public function onCompleteFraction(EditorTaskInfo $info, int $completed, int $total) : void{
		$this->player->get()?->sendTip(TextFormat::GREEN . $info->getName() . TextFormat::GRAY . ": " . TextFormat::LIGHT_PURPLE . $completed . " / " . $total . TextFormat::AQUA . " [" . number_format(($completed / $total) * 100, 2) . "%%]");
	}

	public function onCompletion(EditorTaskInfo $info) : void{
		$this->player->get()?->sendTip(TextFormat::GREEN . $info->getName() . TextFormat::GRAY . ": " . TextFormat::YELLOW . "Completed");
	}
}