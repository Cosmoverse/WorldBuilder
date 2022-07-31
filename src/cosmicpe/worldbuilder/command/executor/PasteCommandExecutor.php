<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\copy\PasteEditorTask;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class PasteCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, \pocketmine\command\Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
		$clipboard = $session->getClipboardSchematic();
		if($clipboard !== null){
			$session->pushEditorTask(new PasteEditorTask($sender->getWorld(), $clipboard, $sender->getPosition()->floor()), TextFormat::GREEN . "Pasting blocks");
		}else{
			$sender->sendMessage(TextFormat::RED . "You must //copy a region first.");
		}
		return true;
	}
}