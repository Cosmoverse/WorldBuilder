<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\SetBiomeEditorTask;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SetBiomeCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/setbiome", "Sets a given biome in selected space");
		$this->setPermission("worldbuilder.command.setbiome");
		$this->addCheck(new RequireSelectionCheck($plugin->getPlayerSessionManager()));
	}

	protected function getBiomeIdFromString(string $string) : ?int{
		if(is_numeric($string)){
			$biome_id = (int) $string;
			if($biome_id >= 0 && $biome_id < 256){
				return $biome_id;
			}
		}
		return null;
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$biome_id = $this->getBiomeIdFromString($args[0]);
			if($biome_id !== null){
				$session = $this->getPlugin()->getPlayerSessionManager()->get($sender);
				$session->pushEditorTask(new SetBiomeEditorTask($sender->getWorld(), $session->getSelection(), $biome_id), TextFormat::GREEN . "Setting biome " . $biome_id);
				return;
			}
			$sender->sendMessage(TextFormat::RED . $args[0] . " is not a valid biome ID.");
			return;
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <block>");
	}
}