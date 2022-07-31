<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\check;

use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class RequireSelectionCheck implements CommandCheck{

	public function __construct(
		private PlayerSessionManager $manager,
		private PlayerOnlyCommandCheck $inner
	){}

	public function validate(CommandSender $sender) : ?string{
		$result = $this->inner->validate($sender);
		if($result !== null){
			return $result;
		}

		assert($sender instanceof Player);
		$selection = $this->manager->get($sender)->getSelection();
		if($selection === null || !$selection->isComplete()){
			return "You must select the required area first.";
		}

		return null;
	}
}