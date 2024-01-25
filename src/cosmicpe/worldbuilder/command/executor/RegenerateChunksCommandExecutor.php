<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\RegenerateChunksEditorTaskInfo;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class RegenerateChunksCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->loader->getPlayerSessionManager()->get($sender);
		$selection = $session->selection;
		$session->pushEditorTask($this->loader->getEditorManager()->buildInstance(new RegenerateChunksEditorTaskInfo(
			$sender->getWorld(),
			$selection->getPoint(0)->getFloorX(),
			$selection->getPoint(0)->getFloorZ(),
			$selection->getPoint(1)->getFloorX(),
			$selection->getPoint(1)->getFloorZ()
		)), TextFormat::GREEN . "Regenerating chunks");
		return true;
	}
}