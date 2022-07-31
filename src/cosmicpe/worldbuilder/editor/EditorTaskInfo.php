<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\task\EditorTask;
use Generator;

final class EditorTaskInfo{

	public static function fromEditorTask(EditorTask $task) : EditorTaskInfo{
		return new EditorTaskInfo($task, $task->run());
	}

	/**
	 * @param EditorTask $task
	 * @param Generator<bool> $generator
	 */
	private function __construct(
		private EditorTask $task,
		private Generator $generator
	){}

	/**
	 * @return Generator<bool>
	 */
	public function getGenerator() : Generator{
		return $this->generator;
	}

	public function getTask() : EditorTask{
		return $this->task;
	}
}