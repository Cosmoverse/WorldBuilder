<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\ReplaceSetRandomEditorTaskInfo;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToWeightedRandomSelectorReplacementMap;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\utils\BlockUtils;
use cosmicpe\worldbuilder\utils\WeightedRandomIntegerSelector;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class ReplaceSetRandomCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		if(isset($args[0])){
			$arg_str = implode(" ", $args); // TODO: Iterate over $args directly to support block identifiers with spaces (do they even have spaces in identifiers?)
			$arg_str_len = strlen($arg_str);
			$offset = 0;

			$replacement_map = new BlockToWeightedRandomSelectorReplacementMap();

			while($offset < $arg_str_len){
				$delimiter_str = "->";
				$delimiter = strpos($arg_str, $delimiter_str, $offset);
				if($delimiter === false){
					$sender->sendMessage(TextFormat::RED . "Failed to find delimiter for \"<block_identifier>\" when parsing: " . substr($arg_str, $offset) . " (at offset: {$offset})");
					return true;
				}

				$find_block_identifier = trim(substr($arg_str, $offset, $delimiter - $offset));
				$find_block = BlockUtils::fromString($find_block_identifier);
				if($find_block === null){
					$sender->sendMessage(TextFormat::RED . "{$find_block_identifier} is not a valid block (failed when parsing: " . substr($arg_str, $delimiter) . " (at offset: {$offset}))");
					return true;
				}

				$offset = $delimiter + strlen($delimiter_str);

				$delimiter_str = "{";
				$delimiter = strpos($arg_str, $delimiter_str, $offset);
				if($delimiter === false){
					$sender->sendMessage(TextFormat::RED . "Failed to find delimiter for \"{replacement}\" (opening bracket) when parsing: " . substr($arg_str, $offset) . " (at offset: {$offset})");
					return true;
				}

				$offset = $delimiter + strlen($delimiter_str);
				$replacement_str_begin = $offset;

				$delimiter_str = "}";
				$delimiter = strpos($arg_str, $delimiter_str, $offset);
				if($delimiter === false){
					$sender->sendMessage(TextFormat::RED . "Failed to find delimiter for \"{replacement}\" (closing bracket) when parsing: " . substr($arg_str, $offset) . " (at offset: {$offset})");
					return true;
				}

				$offset = $delimiter + strlen($delimiter_str);
				$replacement_str = substr($arg_str, $replacement_str_begin, $delimiter - $replacement_str_begin);
				$randomizer = new WeightedRandomIntegerSelector();
				foreach(explode(" ", $replacement_str) as $entry){
					$block_identifier = $entry;
					$weight_symbol_pos = strpos($entry, "@");
					if($weight_symbol_pos === false){
						$weight = 15;
					}else{
						$weight = substr($entry, $weight_symbol_pos + 1);
						if(is_numeric($weight)){
							$weight = (int) $weight;
							if($weight <= 0){
								$sender->sendMessage(TextFormat::RED . "Invalid value supplied for weight in replacement {{$replacement_str}}.");
								return true;
							}
						}else{
							$sender->sendMessage(TextFormat::RED . "Invalid value supplied for weight in replacement {{$replacement_str}}.");
							return true;
						}

						$block_identifier = substr($entry, 0, $weight_symbol_pos);
					}

					$replacement_block = BlockUtils::fromString($block_identifier);
					if($replacement_block === null){
						$sender->sendMessage(TextFormat::RED . "{$block_identifier} is not a valid block (in replacement {{$replacement_str}}).");
						return true;
					}

					$randomizer->add($replacement_block->getStateId(), $weight);
				}

				$randomizer->setup();
				$replacement_map->put($find_block, $randomizer);
			}

			$session = $this->loader->getPlayerSessionManager()->get($sender);
			$manager = $this->loader->getEditorManager();
			$session->pushEditorTask($manager->buildInstance(new ReplaceSetRandomEditorTaskInfo(
				$sender->getWorld(),
				$session->selection->getPoint(0)->getFloorX(),
				$session->selection->getPoint(0)->getFloorY(),
				$session->selection->getPoint(0)->getFloorZ(),
				$session->selection->getPoint(1)->getFloorX(),
				$session->selection->getPoint(1)->getFloorY(),
				$session->selection->getPoint(1)->getFloorZ(),
				$replacement_map,
				$manager->generate_new_chunks
			)), TextFormat::GREEN . "Replacing blocks with a randomized list of block(s)");
			return true;
		}

		$sender->sendMessage(
			TextFormat::RED . "/{$label} <...replacements>" . TextFormat::EOL .
			TextFormat::GRAY . "<replacements> format: <block_identifier>->{replacement}" . TextFormat::EOL .
			TextFormat::GRAY . "{replacement} format: <block_identifier>[@weight=15]" . TextFormat::EOL .
			TextFormat::GRAY . "Example: /{$label} grass->{dirt dirt:1@5 podzol} stone->{stone:1 stone:2@8}"
		);
		return true;
	}
}