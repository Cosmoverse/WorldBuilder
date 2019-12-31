<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\editor\task\listener;

interface EditorTaskListenerInfo{

	public function unregister() : void;
}