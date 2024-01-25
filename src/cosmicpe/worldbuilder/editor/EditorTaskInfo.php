<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\task\EditorTask;
use SOFe\AwaitGenerator\Traverser;

final class EditorTaskInfo{

	public static function fromEditorTask(EditorTask $task) : EditorTaskInfo{
		return new EditorTaskInfo($task, new Traverser($task->run()));
	}

	/**
	 * @param EditorTask $task
	 * @param Traverser<null> $generator
	 */
	private function __construct(
		readonly public EditorTask $task,
		readonly public Traverser $generator
	){}
}