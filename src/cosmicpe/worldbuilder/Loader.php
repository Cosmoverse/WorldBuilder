<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder;

use cosmicpe\worldbuilder\command\CommandManager;
use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{

	protected function onEnable() : void{
		CommandManager::init($this);
		EditorManager::init($this);
		PlayerSessionManager::init($this);
	}

	protected function onDisable() : void{
	}
}