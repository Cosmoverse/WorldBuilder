<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\check;

use cosmicpe\worldbuilder\command\utils\CommandException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class PlayerOnlyCommandCheck implements CommandCheck{

	public function validate(CommandSender $sender) : void{
		if(!($sender instanceof Player)){
			throw new CommandException("This command can only be executed as a player.");
		}
	}
}