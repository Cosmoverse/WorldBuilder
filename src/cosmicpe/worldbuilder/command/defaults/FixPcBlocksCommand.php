<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\ReplaceEditorTask;
use cosmicpe\worldbuilder\editor\utils\replacement\BlockToBlockReplacementMap;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FixPcBlocksCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/fixpcblocks", "Replaces java edition pre-1.12 blocks in selected space with bedrock equivalents");
		$this->setPermission("worldbuilder.command.fixpcblocks");
		$this->addCheck(new RequireSelectionCheck($plugin->getPlayerSessionManager()));
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		$_data = $this->getPlugin()->getResource("pc_pe_fullid_mapping.json");
		$data = json_decode(stream_get_contents($_data), true, 512, JSON_THROW_ON_ERROR);
		fclose($_data);

		$map = new BlockToBlockReplacementMap();
		foreach($data as $pc => $pe){
			$map->putFullId((int) $pc, $pe);
		}

		if(!$map->isEmpty()){
			assert($sender instanceof Player);
			$session = $this->getPlugin()->getPlayerSessionManager()->get($sender);
			$session->pushEditorTask(new ReplaceEditorTask($sender->getWorld(), $session->getSelection(), $map), TextFormat::GREEN . "Replacing " . $map);
			return;
		}
	}
}