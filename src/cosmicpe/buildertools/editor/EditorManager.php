<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\editor;

use cosmicpe\buildertools\editor\task\EditorTask;
use cosmicpe\buildertools\Loader;

final class EditorManager{

	/** @var EditorTaskHandler */
	private static $task_handler;

	public static function init(Loader $plugin) : void{
		self::$task_handler = new EditorTaskHandler();
		$plugin->getScheduler()->scheduleRepeatingTask(self::$task_handler, 1);
	}

	public static function push(EditorTask $task) : void{
		self::$task_handler->handle($task);
	}
}