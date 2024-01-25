<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\SetBiomeEditorTaskInfo;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function is_numeric;

final class SetBiomeCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	private function getBiomeIdFromString(string $string) : ?int{
		if(is_numeric($string)){
			$biome_id = (int) $string;
			if($biome_id >= 0 && $biome_id < 256){
				return $biome_id;
			}
		}
		return null;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$biome_id = $this->getBiomeIdFromString($args[0]);
			if($biome_id !== null){
				$session = $this->loader->getPlayerSessionManager()->get($sender);
				$manager = $this->loader->getEditorManager();
				$session->pushEditorTask($manager->buildInstance(new SetBiomeEditorTaskInfo(
					$sender->getWorld(),
					$session->selection->getPoint(0)->getFloorX(),
					$session->selection->getPoint(0)->getFloorZ(),
					$session->selection->getPoint(1)->getFloorX(),
					$session->selection->getPoint(1)->getFloorZ(),
					$biome_id,
					$manager->generate_new_chunks
				)), TextFormat::GREEN . "Setting biome " . $biome_id);
				return true;
			}
			$sender->sendMessage(TextFormat::RED . $args[0] . " is not a valid biome ID.");
			return true;
		}

		$sender->sendMessage(TextFormat::RED . "/" . $label . " <block>");
		return true;
	}
}