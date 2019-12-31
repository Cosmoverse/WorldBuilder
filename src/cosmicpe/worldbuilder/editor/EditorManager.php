<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\Loader;

final class EditorManager{

	/** @var EditorTaskHandler */
	private static $task_handler;

	public static function init(Loader $plugin) : void{
		self::$task_handler = new EditorTaskHandler((int) $plugin->getConfig()->get("max-ops-per-tick"));
		$plugin->getScheduler()->scheduleRepeatingTask(self::$task_handler, 1);
	}

	public static function push(EditorTask $task) : void{
		self::$task_handler->handle($task);
	}
}