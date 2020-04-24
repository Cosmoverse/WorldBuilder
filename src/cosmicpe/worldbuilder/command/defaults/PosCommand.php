<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\PlayerOnlyCommandCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class PosCommand extends Command{

	/** @var int */
	private $selection_index;

	public function __construct(Loader $plugin, int $selection_index){
		parent::__construct($plugin, "/pos" . ($selection_index + 1), "Selects position #" . ($selection_index + 1));
		$this->selection_index = $selection_index;
		$this->addCheck(new PlayerOnlyCommandCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		$session = PlayerSessionManager::get($sender);
		$selection = $session->getSelection();
		if($selection === null){
			$session->setSelection($selection = new Selection(2));
		}
		$selection->setPoint($this->selection_index, $pos = $sender->getPosition()->floor());
		$sender->sendMessage(TextFormat::GREEN . "Selected position #" . ($this->selection_index + 1) . TextFormat::GRAY . " (" . $pos->x . ", " . $pos->y . ", " . $pos->z . ")");
	}
}