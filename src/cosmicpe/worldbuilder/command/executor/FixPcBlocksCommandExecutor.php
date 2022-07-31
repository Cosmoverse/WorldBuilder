<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\ReplaceEditorTask;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class FixPcBlocksCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		$_data = $this->getLoader()->getResource("pc_pe_fullid_mapping.json");
		$data = json_decode(stream_get_contents($_data), true, 512, JSON_THROW_ON_ERROR);
		fclose($_data);

		$map = new BlockToBlockReplacementMap();
		foreach($data as $pc => $pe){
			$map->putFullId((int) $pc, $pe);
		}

		if(!$map->isEmpty()){
			assert($sender instanceof Player);
			$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
			$session->pushEditorTask(new ReplaceEditorTask($sender->getWorld(), $session->getSelection(), $map), TextFormat::GREEN . "Replacing " . $map);
		}
		return true;
	}
}