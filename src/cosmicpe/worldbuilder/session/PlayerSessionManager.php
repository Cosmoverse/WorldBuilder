<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session;

use cosmicpe\worldbuilder\Loader;
use pocketmine\player\Player;

final class PlayerSessionManager{

	/** @var PlayerSession[] */
	private static $sessions = [];

	public static function init(Loader $plugin) : void{
		$plugin->getServer()->getPluginManager()->registerEvents(new PlayerSessionListener(), $plugin);
	}

	public static function add(Player $player) : void{
		self::$sessions[$player->getId()] = new PlayerSession($player);
	}

	public static function remove(Player $player) : void{
		unset(self::$sessions[$player->getId()]);
	}

	public static function get(Player $player) : PlayerSession{
		return self::$sessions[$player->getId()];
	}
}