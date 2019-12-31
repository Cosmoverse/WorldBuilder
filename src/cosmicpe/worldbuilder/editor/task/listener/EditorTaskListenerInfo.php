<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

interface EditorTaskListenerInfo{

	public function unregister() : void;
}