<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class PosCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private PlayerSessionManager $session_manager,
		readonly private int $selection_index
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->session_manager->get($sender);
		$session->selection ??= new Selection(2);
		$pos = $session->selection->setPoint($this->selection_index, $sender->getPosition()->floor());
		assert($pos !== null);
		$sender->sendMessage(TextFormat::GREEN . "Selected position #" . ($this->selection_index + 1) . TextFormat::GRAY . " (" . $pos->x . ", " . $pos->y . ", " . $pos->z . ")");
		return true;
	}
}