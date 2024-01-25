<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\SetBiomeEditorTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SetBiomeCommandExecutor extends WorldBuilderCommandExecutor{

	private function getBiomeIdFromString(string $string) : ?int{
		if(is_numeric($string)){
			$biome_id = (int) $string;
			if($biome_id >= 0 && $biome_id < 256){
				return $biome_id;
			}
		}
		return null;
	}

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$biome_id = $this->getBiomeIdFromString($args[0]);
			if($biome_id !== null){
				$session = $this->loader->getPlayerSessionManager()->get($sender);
				$session->pushEditorTask(new SetBiomeEditorTask($sender->getWorld(), $session->selection, $biome_id), TextFormat::GREEN . "Setting biome " . $biome_id);
				return true;
			}
			$sender->sendMessage(TextFormat::RED . $args[0] . " is not a valid biome ID.");
			return true;
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <block>");
		return true;
	}
}