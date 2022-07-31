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
		$this->setPermission("worldbuilder.command.regeneratechunks");
		$this->addCheck(new RequireSelectionCheck($plugin->getPlayerSessionManager()));
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		$session = $this->getPlugin()->getPlayerSessionManager()->get($sender);
		$selection = $session->getSelection();
		$session->pushEditorTask(new RegenerateChunksEditorTask($sender->getWorld(), $selection), TextFormat::GREEN . "Regenerating chunks");
	}
}