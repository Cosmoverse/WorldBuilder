<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\command\check\CommandCheck;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

final class WorldBuilderCommandExecutor implements CommandExecutor{

	/**
	 * @param CommandExecutor $inner
	 * @param list<CommandCheck> $checks
	 */
	public function __construct(
		readonly private CommandExecutor $inner,
		readonly private array $checks
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		foreach($this->checks as $check){
			$error = $check->validate($sender);
			if($error !== null){
				$sender->sendMessage(TextFormat::RED . $error);
				return true;
			}
		}
		return $this->inner->onCommand($sender, $command, $label, $args);
	}
}