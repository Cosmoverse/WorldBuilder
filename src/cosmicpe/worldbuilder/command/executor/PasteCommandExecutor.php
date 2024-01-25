<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\copy\PasteEditorTask;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class PasteCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->loader->getPlayerSessionManager()->get($sender);
		$clipboard = $session->clipboard_schematic;
		if($clipboard !== null){
			$session->pushEditorTask(new PasteEditorTask($sender->getWorld(), $clipboard, $sender->getPosition()->floor(), $this->loader->getEditorManager()->generate_new_chunks), TextFormat::GREEN . "Pasting blocks");
		}else{
			$sender->sendMessage(TextFormat::RED . "You must //copy a region first.");
		}
		return true;
	}
}