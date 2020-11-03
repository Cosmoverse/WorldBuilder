<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\format\EditorFormatRegistry;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\Loader;

final class EditorManager{

	/** @var EditorTaskHandler */
	private static $task_handler;

	/** @var EditorFormatRegistry */
	private static $format_registry;

	public static function init(Loader $plugin) : void{
		NamedtagCopierManager::init();

		self::$format_registry = new EditorFormatRegistry();
		self::$task_handler = new EditorTaskHandler((int) $plugin->getConfig()->get("max-ops-per-tick"));
		$plugin->getScheduler()->scheduleRepeatingTask(self::$task_handler, 1);
	}

	public static function getFormatRegistry() : EditorFormatRegistry{
		return self::$format_registry;
	}

	public static function push(EditorTask $task) : void{
		self::$task_handler->handle($task);
	}
}