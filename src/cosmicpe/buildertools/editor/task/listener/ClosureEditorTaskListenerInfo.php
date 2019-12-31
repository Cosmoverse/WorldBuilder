<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\editor\task\listener;

use Closure;

class ClosureEditorTaskListenerInfo implements EditorTaskListenerInfo{

	/** @var Closure */
	private $unregisterer;

	public function __construct(Closure $unregisterer){
		$this->unregisterer = $unregisterer;
	}

	public function unregister() : void{
		($this->unregisterer)();
	}
}