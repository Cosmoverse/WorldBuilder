<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\format\EditorFormatRegistry;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\Loader;

final class EditorManager{

	private EditorTaskHandler $task_handler;
	private EditorFormatRegistry $format_registry;

	public function __construct(){
		NamedtagCopierManager::init();
		$this->format_registry = new EditorFormatRegistry();
	}

	public function init(Loader $plugin) : void{
		$this->task_handler = new EditorTaskHandler((int) $plugin->getConfig()->get("max-ops-per-tick"));
		$plugin->getScheduler()->scheduleRepeatingTask($this->task_handler, 1);
	}

	public function getFormatRegistry() : EditorFormatRegistry{
		return $this->format_registry;
	}

	public function push(EditorTask $task) : void{
		$this->task_handler->handle($task);
	}
}