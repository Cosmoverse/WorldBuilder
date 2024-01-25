<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\editor\task\ReplaceEditorTask;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;

final class DrainCommandExecutor implements CommandExecutor{

	readonly private BlockToBlockReplacementMap $map;

	public function __construct(
		readonly private PlayerSessionManager $session_manager,
		readonly private RequireSelectionCheck $selection_check
	){
		$this->map = new BlockToBlockReplacementMap();
		$air = VanillaBlocks::AIR();
		foreach(RuntimeBlockStateRegistry::getInstance()->getAllKnownStates() as $state){
			if($state instanceof Water){
				$this->map->put($state, $air);
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->session_manager->get($sender);
		if(!isset($args[0])){
			$result = $this->selection_check->validate($sender);
			if($result !== null){
				$sender->sendMessage(TextFormat::RED . $result);
				return true;
			}

			$selection = $session->selection;
			$message = "Draining water";
		}else{
			$radius = (int) $args[0];
			if($radius < 0){
				$sender->sendMessage(TextFormat::RED . "Usage: /" . $label . " [radius]");
				return true;
			}

			$max_radius = $sender->getViewDistance() << Chunk::COORD_BIT_SIZE;
			if($radius > $max_radius){
				$radius = $max_radius;
			}

			$selection = Selection::cuboidalRadius($sender->getPosition(), $radius);
			$message = "Draining water in " . $radius . " block" . ($radius === 1 ? "" : "s") . " radius";
		}

		$session->pushEditorTask(new ReplaceEditorTask($sender->getWorld(), $selection, $this->map), TextFormat::GREEN . $message);
		return true;
	}
}