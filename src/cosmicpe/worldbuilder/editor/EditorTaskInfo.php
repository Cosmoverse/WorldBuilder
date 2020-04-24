<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\task\EditorTask;
use Generator;

final class EditorTaskInfo{

	public static function fromEditorTask(EditorTask $task) : EditorTaskInfo{
		return new EditorTaskInfo($task, $task->run());
	}

	/** @var EditorTask */
	private $task;

	/** @var Generator<bool> */
	private $generator;

	/**
	 * @param EditorTask $task
	 * @param Generator<bool> $generator
	 */
	private function __construct(EditorTask $task, Generator $generator){
		$this->task = $task;
		$this->generator = $generator;
	}

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