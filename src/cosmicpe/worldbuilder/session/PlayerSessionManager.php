<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session;

use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\Loader;
use pocketmine\player\Player;

final class PlayerSessionManager{

	private EditorManager $editor_manager;

	/** @var array<int, PlayerSession> */
	private array $sessions = [];
	
	public function __construct(){
	}

	public function init(Loader $plugin) : void{
		$plugin->getServer()->getPluginManager()->registerEvents(new PlayerSessionListener($this), $plugin);
		$this->editor_manager = $plugin->getEditorManager();
	}

	public function add(Player $player) : void{
		$this->sessions[$player->getId()] = new PlayerSession($player, $this->editor_manager);
	}

	public function remove(Player $player) : void{
		unset($this->sessions[$player->getId()]);
	}

	public function get(Player $player) : PlayerSession{
		return $this->sessions[$player->getId()];
	}
}