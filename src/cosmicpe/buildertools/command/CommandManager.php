<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\command;

use cosmicpe\buildertools\command\defaults\PosCommand;
use cosmicpe\buildertools\command\defaults\ReplaceCommand;
use cosmicpe\buildertools\command\defaults\SetCommand;
use cosmicpe\buildertools\Loader;

final class CommandManager{

	public static function init(Loader $plugin) : void{
		self::register($plugin,
			new PosCommand($plugin, 0),
			new PosCommand($plugin, 1),
			new ReplaceCommand($plugin),
			new SetCommand($plugin)
		);
	}

	public static function register(Loader $plugin, Command ...$commands) : void{
		$plugin->getServer()->getCommandMap()->registerAll($plugin->getName(), $commands);
	}
}