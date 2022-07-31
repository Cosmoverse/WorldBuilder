<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\check;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class PlayerOnlyCommandCheck implements CommandCheck{

	public function __construct(){
	}

	public function validate(CommandSender $sender) : ?string{
		return $sender instanceof Player ? null : "This command can only be executed as a player.";
	}
}