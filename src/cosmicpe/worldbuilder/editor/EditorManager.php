<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\format\EditorFormatRegistry;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\Loader;
use pocketmine\scheduler\TaskScheduler;

final class EditorManager{

	private ?EditorTaskHandler $task_handler = null;
	private EditorFormatRegistry $format_registry;
	private TaskScheduler $scheduler;
	private int $max_ops_per_tick;

	public function __construct(){
		NamedtagCopierManager::init();
		$this->format_registry = new EditorFormatRegistry();
	}

	public function init(Loader $plugin) : void{
		$this->scheduler = $plugin->getScheduler();
		$this->max_ops_per_tick = (int) $plugin->getConfig()->get("max-ops-per-tick");
	}

	public function getFormatRegistry() : EditorFormatRegistry{
		return $this->format_registry;
	}

	public function push(EditorTask $task) : void{
		if($this->task_handler === null){
			$this->task_handler = new EditorTaskHandler($this->max_ops_per_tick, function() : void{
				$this->task_handler = null;
			});
			$this->scheduler->scheduleRepeatingTask($this->task_handler, 1);
		}
		$this->task_handler->handle($task);
	}
}