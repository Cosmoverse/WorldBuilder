<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\ReplaceEditorTask;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use cosmicpe\worldbuilder\utils\BlockUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class ReplaceCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		if(isset($args[0], $args[1]) && (count($args) & 1) === 0){
			$map = new BlockToBlockReplacementMap();

			foreach(array_chunk($args, 2) as [$find, $replace]){
				$find_block = BlockUtils::fromString($find);
				if($find_block === null){
					$sender->sendMessage(TextFormat::RED . $find . " is not a valid block.");
					return true;
				}

				$replace_block = BlockUtils::fromString($replace);
				if($replace_block === null){
					$sender->sendMessage(TextFormat::RED . $replace . " is not a valid block.");
					return true;
				}

				$map->put($find_block, $replace_block);
			}

			if(!$map->isEmpty()){
				$session = $this->loader->getPlayerSessionManager()->get($sender);
				$session->pushEditorTask(new ReplaceEditorTask($sender->getWorld(), $session->selection, $map), TextFormat::GREEN . "Replacing " . $map);
				return true;
			}
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <...<find> <replace>>");
		return true;
	}
}