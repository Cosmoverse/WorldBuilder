<?php

declare(strict_types=1);

namespace cosmicpe\buildertools;

use cosmicpe\buildertools\command\CommandManager;
use cosmicpe\buildertools\editor\EditorManager;
use cosmicpe\buildertools\session\PlayerSessionManager;
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