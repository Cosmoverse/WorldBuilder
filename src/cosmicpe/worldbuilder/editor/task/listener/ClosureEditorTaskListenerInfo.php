<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use Closure;

class ClosureEditorTaskListenerInfo implements EditorTaskListenerInfo{

	private Closure $unregisterer;

	public function __construct(Closure $unregisterer){
		$this->unregisterer = $unregisterer;
	}

	public function unregister() : void{
		($this->unregisterer)();
	}
}