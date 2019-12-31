<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\editor;

use cosmicpe\buildertools\editor\task\EditorTask;
use Generator;

final class EditorTaskInfo{

	public static function fromEditorTask(EditorTask $task) : EditorTaskInfo{
		return new EditorTaskInfo($task, $task->run());
	}

	/** @var EditorTask */
	private $task;

	/** @var Generator */
	private $generator;

	private function __construct(EditorTask $task, Generator $generator){
		$this->task = $task;
		$this->generator = $generator;
	}

	public function getGenerator() : Generator{
		return $this->generator;
	}

	public function getTask() : EditorTask{
		return $this->task;
	}
}