<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\SetRandomEditorTask;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\utils\BlockUtils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SetRandomCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/setrandom", "Sets a randomized list of block in selected space");
		$this->setPermission("worldbuilder.command.setrandom");
		$this->addCheck(new RequireSelectionCheck($plugin->getPlayerSessionManager()));
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$randomizer = new WeightedRandomIntegerSelector();

			foreach($args as $arg){
				$block_identifier = $arg;

				$weight_symbol_pos = strpos($arg, "@");
				if($weight_symbol_pos !== false){
					$weight = substr($arg, $weight_symbol_pos + 1);
					if(is_numeric($weight)){
						$weight = (int) $weight;
						if($weight <= 0){
							$sender->sendMessage(TextFormat::RED . "Invalid value supplied for weight in \"{$arg}\".");
							return;
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "Invalid value supplied for weight in \"{$arg}\".");
						return;
					}

					$block_identifier = substr($arg, 0, $weight_symbol_pos);
				}else{
					$weight = 15;
				}

				$block = BlockUtils::fromString($block_identifier);
				if($block === null){
					$sender->sendMessage(TextFormat::RED . "{$block_identifier} is not a valid block (in \"{$arg}\").");
					return;
				}

				$randomizer->add($block->getFullId(), $weight);
			}

			$randomizer->setup();
			$session = $this->getPlugin()->getPlayerSessionManager()->get($sender);
			$session->pushEditorTask(new SetRandomEditorTask($sender->getWorld(), $session->getSelection(), $randomizer), TextFormat::GREEN . "Setting a randomized list of {$randomizer->count()} block(s)");
			return;
		}

		$sender->sendMessage(
			TextFormat::RED . "/{$label} <...block>" . TextFormat::EOL .
			TextFormat::GRAY . "<block> format: <block_identifier>[@weight=15]" . TextFormat::EOL .
			TextFormat::GRAY . "Example: /{$label} grass@18 wool:1@4 dirt podzol"
		);
	}
}