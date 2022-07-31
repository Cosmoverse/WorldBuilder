<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\session;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class PlayerSessionListener implements Listener{
	
	public function __construct(
		private PlayerSessionManager $manager
	){}

	/**
	 * @param PlayerLoginEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerLogin(PlayerLoginEvent $event) : void{
		$this->manager->add($event->getPlayer());
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$this->manager->remove($event->getPlayer());
	}
}