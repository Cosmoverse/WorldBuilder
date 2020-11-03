<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\PlayerOnlyCommandCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\copy\PasteEditorTask;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class PasteCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/paste", "Pastes blocks in the selected space");
		$this->setPermission("worldbuilder.command.paste");
		$this->addCheck(new PlayerOnlyCommandCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		$session = PlayerSessionManager::get($sender);
		$clipboard = $session->getClipboardSchematic();
		if($clipboard !== null){
			$session->pushEditorTask(new PasteEditorTask($sender->getWorld(), $clipboard, $sender->getPosition()->floor()), TextFormat::GREEN . "Pasting blocks");
		}else{
			$sender->sendMessage(TextFormat::RED . "You must //copy a region first.");
		}
	}
}