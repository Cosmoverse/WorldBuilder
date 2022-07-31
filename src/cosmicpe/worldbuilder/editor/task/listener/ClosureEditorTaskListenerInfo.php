<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use Closure;

class ClosureEditorTaskListenerInfo implements EditorTaskListenerInfo{

	public function __construct(
		private Closure $unregisterer
	){}

	public function unregister() : void{
		($this->unregisterer)();
	}
}