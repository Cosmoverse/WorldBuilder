<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\SetEditorTask;
use cosmicpe\worldbuilder\utils\BlockUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SetCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, \pocketmine\command\Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$block = BlockUtils::fromString($args[0]);
			if($block !== null){
				$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
				$session->pushEditorTask(new SetEditorTask($sender->getWorld(), $session->getSelection(), $block), TextFormat::GREEN . "Setting " . $block->getName());
				return true;
			}
			$sender->sendMessage(TextFormat::RED . $args[0] . " is not a valid block.");
			return true;
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <block>");
		return true;
	}
}