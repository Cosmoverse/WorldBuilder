<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\check;

use cosmicpe\worldbuilder\command\utils\CommandException;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RequireSelectionCheck extends PlayerOnlyCommandCheck{

	public function validate(CommandSender $sender) : void{
		parent::validate($sender);
		/** @var Player $sender */
		$selection = PlayerSessionManager::get($sender)->getSelection();
		if($selection === null || !$selection->isComplete()){
			throw new CommandException("You must select the required area first.");
		}
	}
}