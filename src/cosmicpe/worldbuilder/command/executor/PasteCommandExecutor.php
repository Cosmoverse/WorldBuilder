<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\PasteEditorTaskInfo;
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
			$relative = $sender->getPosition();
			$manager = $this->loader->getEditorManager();
			$session->pushEditorTask($manager->buildInstance(new PasteEditorTaskInfo(
				$sender->getWorld(),
				$clipboard,
				$relative->getFloorX(),
				$relative->getFloorY(),
				$relative->getFloorZ(),
				$manager->generate_new_chunks
			)), TextFormat::GREEN . "Pasting blocks");
		}else{
			$sender->sendMessage(TextFormat::RED . "You must //copy a region first.");
		}
		return true;
	}
}