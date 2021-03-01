<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\PlayerOnlyCommandCheck;
use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\ReplaceEditorTask;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\block\BlockFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class DrainCommand extends Command{

	/** @var RequireSelectionCheck */
	private $selection_check;

	/** @var BlockToBlockReplacementMap */
	private $map;

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/drain", "Drain water in selected space or a radius");
		$this->setPermission("worldbuilder.command.drain");
		$this->addCheck(new PlayerOnlyCommandCheck());

		$this->selection_check = new RequireSelectionCheck();

		$this->map = new BlockToBlockReplacementMap();
		$air = VanillaBlocks::AIR();
		foreach(BlockFactory::getInstance()->getAllKnownStates() as $state){
			if($state instanceof Water){
				$this->map->put($state, $air);
			}
		}
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		$session = PlayerSessionManager::get($sender);
		if(!isset($args[0])){
			$this->selection_check->validate($sender);
			$selection = $session->getSelection();
			$message = "Draining water";
		}else{
			$radius = (int) $args[0];
			if($radius < 0){
				$sender->sendMessage(TextFormat::RED . "Usage: /" . $label . " [radius]");
				return;
			}

			$max_radius = $sender->getViewDistance() << 4;
			if($radius > $max_radius){
				$radius = $max_radius;
			}

			$selection = Selection::cuboidalRadius($sender->getPosition(), $radius);
			$message = "Draining water in " . $radius . " block" . ($radius === 1 ? "" : "s") . " radius";
		}

		$session->pushEditorTask(new ReplaceEditorTask($sender->getWorld(), $selection, $this->map), TextFormat::GREEN . $message);
	}
}