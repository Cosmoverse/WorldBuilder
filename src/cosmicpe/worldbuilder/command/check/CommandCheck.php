<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\check;

use cosmicpe\worldbuilder\command\utils\CommandException;
use pocketmine\command\CommandSender;

interface CommandCheck{

	/**
	 * @param CommandSender $sender
	 * @return string
	 */
	public function validate(CommandSender $sender) : ?string;
}