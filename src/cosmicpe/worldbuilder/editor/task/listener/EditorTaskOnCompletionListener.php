<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use Closure;
use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;

final class EditorTaskOnCompletionListener implements EditorTaskListener{

	/**
	 * @param Closure(EditorTaskInfo) : void $callback
	 */
	public function __construct(
		readonly private Closure $callback
	){}

	public function onRegister(EditorTaskInfo $info) : void{
	}

	public function onCompleteFraction(EditorTaskInfo $info, int $completed, int $total) : void{
	}

	public function onCompletion(EditorTaskInfo $info) : void{
		($this->callback)($info);
	}
}