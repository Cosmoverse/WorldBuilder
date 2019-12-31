<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\ReplaceEditorTask;
use cosmicpe\worldbuilder\editor\utils\ReplacementMap;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\utils\BlockUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ReplaceCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/replace", "Replaces blocks in selected position");
		$this->addCheck(new RequireSelectionCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		/** @var Player $sender */
		if(isset($args[0], $args[1])){
			$map = new ReplacementMap();

			foreach(array_chunk($args, 2) as [$find, $replace]){
				$find_block = BlockUtils::fromString($find);
				if($find_block === null){
					$sender->sendMessage(TextFormat::RED . $find . " is not a valid block.");
					return;
				}

				$replace_block = BlockUtils::fromString($replace);
				if($replace_block === null){
					$sender->sendMessage(TextFormat::RED . $replace . " is not a valid block.");
					return;
				}

				$map->put($find_block, $replace_block);
			}

			if(!$map->isEmpty()){
				$session = PlayerSessionManager::get($sender);
				$session->pushEditorTask(new ReplaceEditorTask($sender->getWorld(), $session->getSelection(), $map), TextFormat::GREEN . "Replacing " . $map);
				return;
			}
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <...<find> <replace>>");
	}
}