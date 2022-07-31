<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class PosCommandExecutor extends WorldBuilderCommandExecutor{

	public function __construct(
		Loader $loader,
		array $checks,
		private int $selection_index
	){
		parent::__construct($loader, $checks);
	}

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
		$selection = $session->getSelection();
		if($selection === null){
			$session->setSelection($selection = new Selection(2));
		}

		$pos = $selection->setPoint($this->selection_index, $sender->getPosition()->floor());
		assert($pos !== null);
		$sender->sendMessage(TextFormat::GREEN . "Selected position #" . ($this->selection_index + 1) . TextFormat::GRAY . " (" . $pos->x . ", " . $pos->y . ", " . $pos->z . ")");
		return true;
	}
}