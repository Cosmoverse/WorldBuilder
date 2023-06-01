<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\SetRandomEditorTask;
use cosmicpe\worldbuilder\utils\BlockUtils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SetRandomCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, \pocketmine\command\Command $command, string $label, array $args) : bool{
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
							return true;
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "Invalid value supplied for weight in \"{$arg}\".");
						return true;
					}

					$block_identifier = substr($arg, 0, $weight_symbol_pos);
				}else{
					$weight = 15;
				}

				$block = BlockUtils::fromString($block_identifier);
				if($block === null){
					$sender->sendMessage(TextFormat::RED . "{$block_identifier} is not a valid block (in \"{$arg}\").");
					return true;
				}

				$randomizer->add($block->getStateId(), $weight);
			}

			$randomizer->setup();
			$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
			$session->pushEditorTask(new SetRandomEditorTask($sender->getWorld(), $session->getSelection(), $randomizer), TextFormat::GREEN . "Setting a randomized list of {$randomizer->count()} block(s)");
			return true;
		}

		$sender->sendMessage(
			TextFormat::RED . "/{$label} <...block>" . TextFormat::EOL .
			TextFormat::GRAY . "<block> format: <block_identifier>[@weight=15]" . TextFormat::EOL .
			TextFormat::GRAY . "Example: /{$label} grass@18 wool:1@4 dirt podzol"
		);
		return true;
	}
}