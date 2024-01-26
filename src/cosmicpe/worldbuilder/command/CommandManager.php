<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command;

use cosmicpe\worldbuilder\command\check\CommandCheck;
use cosmicpe\worldbuilder\command\check\PlayerOnlyCommandCheck;
use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\executor\CopyCommandExecutor;
use cosmicpe\worldbuilder\command\executor\DrainCommandExecutor;
use cosmicpe\worldbuilder\command\executor\PasteCommandExecutor;
use cosmicpe\worldbuilder\command\executor\PosCommandExecutor;
use cosmicpe\worldbuilder\command\executor\RegenerateChunksCommandExecutor;
use cosmicpe\worldbuilder\command\executor\ReplaceCommandExecutor;
use cosmicpe\worldbuilder\command\executor\ReplaceSetRandomCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SchematicCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SetBiomeCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SetCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SetRandomCommandExecutor;
use cosmicpe\worldbuilder\command\executor\WorldBuilderCommandExecutor;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\CommandExecutor;
use pocketmine\command\PluginCommand;
use RuntimeException;

final class CommandManager{

	public function __construct(
		readonly private Loader $loader
	){}

	public function init() : void{
		$check_player_only = new PlayerOnlyCommandCheck();
		$check_require_selection = new RequireSelectionCheck($this->loader->getPlayerSessionManager(), $check_player_only);
		$session_manager = $this->loader->getPlayerSessionManager();
		$buffer_clipboard_operations = (bool) $this->loader->getConfig()->get("buffer-clipboard-operations", true);

		$this->setExecutor("/copy", new CopyCommandExecutor($this->loader, $buffer_clipboard_operations), [$check_require_selection]);
		$this->setExecutor("/drain", new DrainCommandExecutor($this->loader, $check_require_selection), [$check_player_only]);
		$this->setExecutor("/paste", new PasteCommandExecutor($this->loader), [$check_require_selection]);
		$this->setExecutor("/pos1", new PosCommandExecutor($session_manager, 0), [$check_player_only]);
		$this->setExecutor("/pos2", new PosCommandExecutor($session_manager, 1), [$check_player_only]);
		$this->setExecutor("/regeneratechunks", new RegenerateChunksCommandExecutor($this->loader), [$check_require_selection]);
		$this->setExecutor("/replace", new ReplaceCommandExecutor($this->loader), [$check_require_selection]);
		$this->setExecutor("/replacesetrandom", new ReplaceSetRandomCommandExecutor($this->loader), [$check_require_selection]);
		$this->setExecutor("/schematic", new SchematicCommandExecutor($this->loader), [$check_player_only]);
		$this->setExecutor("/set", new SetCommandExecutor($this->loader), [$check_require_selection]);
		$this->setExecutor("/setbiome", new SetBiomeCommandExecutor($this->loader), [$check_require_selection]);
		$this->setExecutor("/setrandom", new SetRandomCommandExecutor($this->loader), [$check_require_selection]);
	}

	/**
	 * @param string $command
	 * @param CommandExecutor $executor
	 * @param list<CommandCheck> $checks
	 */
	private function setExecutor(string $command, CommandExecutor $executor, array $checks) : void{
		$result = $this->loader->getCommand($command);
		$result instanceof PluginCommand || throw new RuntimeException("Could not obtain command: {$command}");
		$result->setExecutor(new WorldBuilderCommandExecutor($executor, $checks));
	}
}