<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder;

use cosmicpe\worldbuilder\command\CommandManager;
use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{

	private CommandManager $command_manager;
	private EditorManager $editor_manager;
	private PlayerSessionManager $player_session_manager;

	protected function onLoad() : void{
		$this->command_manager = new CommandManager();
		$this->editor_manager = new EditorManager();
		$this->player_session_manager = new PlayerSessionManager();
	}

	protected function onEnable() : void{
		$this->command_manager->init($this);
		$this->editor_manager->init($this);
		$this->player_session_manager->init($this);
	}

	protected function onDisable() : void{
	}

	public function getCommandManager() : CommandManager{
		return $this->command_manager;
	}

	public function getEditorManager() : EditorManager{
		return $this->editor_manager;
	}

	public function getPlayerSessionManager() : PlayerSessionManager{
		return $this->player_session_manager;
	}
}