<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command;

use cosmicpe\worldbuilder\command\defaults\CopyCommand;
use cosmicpe\worldbuilder\command\defaults\DrainCommand;
use cosmicpe\worldbuilder\command\defaults\FixPcBlocksCommand;
use cosmicpe\worldbuilder\command\defaults\PasteCommand;
use cosmicpe\worldbuilder\command\defaults\PosCommand;
use cosmicpe\worldbuilder\command\defaults\RegenerateChunksCommand;
use cosmicpe\worldbuilder\command\defaults\ReplaceCommand;
use cosmicpe\worldbuilder\command\defaults\ReplaceSetRandomCommand;
use cosmicpe\worldbuilder\command\defaults\SchematicCommand;
use cosmicpe\worldbuilder\command\defaults\SetBiomeCommand;
use cosmicpe\worldbuilder\command\defaults\SetCommand;
use cosmicpe\worldbuilder\command\defaults\SetRandomCommand;
use cosmicpe\worldbuilder\Loader;

final class CommandManager{

	public static function init(Loader $plugin) : void{
		self::register($plugin,
			new CopyCommand($plugin),
			new RegenerateChunksCommand($plugin),
			new DrainCommand($plugin),
			new FixPcBlocksCommand($plugin),
			new PasteCommand($plugin),
			new PosCommand($plugin, 0),
			new PosCommand($plugin, 1),
			new ReplaceCommand($plugin),
			new ReplaceSetRandomCommand($plugin),
			new SchematicCommand($plugin),
			new SetBiomeCommand($plugin),
			new SetCommand($plugin),
			new SetRandomCommand($plugin)
		);
	}

	public static function register(Loader $plugin, Command ...$commands) : void{
		$plugin->getServer()->getCommandMap()->registerAll($plugin->getName(), $commands);
	}
}