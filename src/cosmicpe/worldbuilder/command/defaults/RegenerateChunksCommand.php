<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\RegenerateChunksEditorTask;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class RegenerateChunksCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/regeneratechunks", "Regenerates all chunks in the selected space");
		$this->addCheck(new RequireSelectionCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		/** @var Player $sender */
		$session = PlayerSessionManager::get($sender);
		$selection = $session->getSelection();
		$session->pushEditorTask(new RegenerateChunksEditorTask($sender->getWorld(), $selection), TextFormat::GREEN . "Regenerating chunks");
	}
}