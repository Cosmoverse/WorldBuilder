<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\command\check\CommandCheck;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

abstract class WorldBuilderCommandExecutor implements CommandExecutor{

	/**
	 * @param CommandCheck[] $checks
	 */
	public function __construct(
		private Loader $loader,
		private array $checks
	){}

	public function getLoader() : Loader{
		return $this->loader;
	}

	final public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		foreach($this->checks as $check){
			$error = $check->validate($sender);
			if($error !== null){
				$sender->sendMessage(TextFormat::RED . $error);
				return true;
			}
		}

		return $this->executeCommand($sender, $command, $label, $args);
	}

	/**
	 * @param CommandSender $sender
	 * @param Command $command
	 * @param string $label
	 * @param string[] $args
	 * @return bool
	 */
	abstract protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool;
}