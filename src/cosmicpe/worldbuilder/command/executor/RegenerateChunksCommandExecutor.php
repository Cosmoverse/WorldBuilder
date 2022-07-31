<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\RegenerateChunksEditorTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class RegenerateChunksCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
		$selection = $session->getSelection();
		$session->pushEditorTask(new RegenerateChunksEditorTask($sender->getWorld(), $selection), TextFormat::GREEN . "Regenerating chunks");
		return true;
	}
}