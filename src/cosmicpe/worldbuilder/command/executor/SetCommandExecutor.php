<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\SetEditorTaskInfo;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\utils\BlockUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SetCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$block = BlockUtils::fromString($args[0]);
			if($block !== null){
				$session = $this->loader->getPlayerSessionManager()->get($sender);
				$manager = $this->loader->getEditorManager();
				$session->pushEditorTask($manager->buildInstance(new SetEditorTaskInfo(
					$sender->getWorld(),
					$session->selection->getPoint(0)->getFloorX(),
					$session->selection->getPoint(0)->getFloorY(),
					$session->selection->getPoint(0)->getFloorZ(),
					$session->selection->getPoint(1)->getFloorX(),
					$session->selection->getPoint(1)->getFloorY(),
					$session->selection->getPoint(1)->getFloorZ(),
					$block,
					$manager->generate_new_chunks
				)), TextFormat::GREEN . "Setting " . $block->getName());
				return true;
			}
			$sender->sendMessage(TextFormat::RED . $args[0] . " is not a valid block.");
			return true;
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <block>");
		return true;
	}
}