<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use Closure;

class ClosureEditorTaskListenerInfo implements EditorTaskListenerInfo{

	/**
	 * @param Closure() : void $unregisterer
	 */
	public function __construct(
		readonly private Closure $unregisterer
	){}

	public function unregister() : void{
		($this->unregisterer)();
	}
}