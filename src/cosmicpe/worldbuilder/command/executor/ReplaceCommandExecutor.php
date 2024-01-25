<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\ReplaceEditorTaskInfo;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\utils\BlockUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class ReplaceCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
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
				$manager = $this->loader->getEditorManager();
				$session->pushEditorTask($manager->buildInstance(new ReplaceEditorTaskInfo(
					$sender->getWorld(),
					$session->selection->getPoint(0)->getFloorX(),
					$session->selection->getPoint(0)->getFloorY(),
					$session->selection->getPoint(0)->getFloorZ(),
					$session->selection->getPoint(1)->getFloorX(),
					$session->selection->getPoint(1)->getFloorY(),
					$session->selection->getPoint(1)->getFloorZ(),
					$map,
					$manager->generate_new_chunks
				)), TextFormat::GREEN . "Replacing " . $map);
				return true;
			}
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <...<find> <replace>>");
		return true;
	}
}