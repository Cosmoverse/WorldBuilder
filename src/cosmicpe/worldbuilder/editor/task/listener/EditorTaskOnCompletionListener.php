<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use Closure;
use cosmicpe\worldbuilder\editor\task\EditorTask;

class EditorTaskOnCompletionListener implements EditorTaskListener{

	/**
	 * @param Closure(EditorTask) : void $callback
	 */
	public function __construct(
		private Closure $callback
	){}

	public function onRegister(EditorTask $task) : void{
	}

	public function onCompleteFraction(EditorTask $task, int $completed, int $total) : void{
	}

	public function onCompletion(EditorTask $task) : void{
		($this->callback)($task);
	}
}