<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command;

use cosmicpe\worldbuilder\command\check\PlayerOnlyCommandCheck;
use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\executor\CopyCommandExecutor;
use cosmicpe\worldbuilder\command\executor\DrainCommandExecutor;
use cosmicpe\worldbuilder\command\executor\FixPcBlocksCommandExecutor;
use cosmicpe\worldbuilder\command\executor\PasteCommandExecutor;
use cosmicpe\worldbuilder\command\executor\PosCommandExecutor;
use cosmicpe\worldbuilder\command\executor\RegenerateChunksCommandExecutor;
use cosmicpe\worldbuilder\command\executor\ReplaceCommandExecutor;
use cosmicpe\worldbuilder\command\executor\ReplaceSetRandomCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SchematicCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SetBiomeCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SetCommandExecutor;
use cosmicpe\worldbuilder\command\executor\SetRandomCommandExecutor;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\PluginCommand;
use RuntimeException;

final class CommandManager{

	public function __construct(
		private Loader $loader
	){}

	public function init() : void{
		$check_player_only = new PlayerOnlyCommandCheck();
		$check_require_selection = new RequireSelectionCheck($this->loader->getPlayerSessionManager(), $check_player_only);

		$this->getCommand("/copy")->setExecutor(new CopyCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/drain")->setExecutor(new DrainCommandExecutor($this->loader, [$check_player_only], $check_require_selection));
		$this->getCommand("/fixpcblocks")->setExecutor(new FixPcBlocksCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/paste")->setExecutor(new PasteCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/pos1")->setExecutor(new PosCommandExecutor($this->loader, [$check_player_only], 0));
		$this->getCommand("/pos2")->setExecutor(new PosCommandExecutor($this->loader, [$check_player_only], 1));
		$this->getCommand("/regeneratechunks")->setExecutor(new RegenerateChunksCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/replace")->setExecutor(new ReplaceCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/replacesetrandom")->setExecutor(new ReplaceSetRandomCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/schematic")->setExecutor(new SchematicCommandExecutor($this->loader, [$check_player_only]));
		$this->getCommand("/set")->setExecutor(new SetCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/setbiome")->setExecutor(new SetBiomeCommandExecutor($this->loader, [$check_require_selection]));
		$this->getCommand("/setrandom")->setExecutor(new SetRandomCommandExecutor($this->loader, [$check_require_selection]));
	}

	private function getCommand(string $command) : PluginCommand{
		$result = $this->loader->getCommand($command);
		if(!($result instanceof PluginCommand)){
			throw new RuntimeException("Could not obtain command: {$command}");
		}
		return $result;
	}
}