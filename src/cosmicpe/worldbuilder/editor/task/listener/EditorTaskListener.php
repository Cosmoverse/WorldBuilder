<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use cosmicpe\worldbuilder\editor\task\EditorTask;

interface EditorTaskListener{

	public function onRegister(EditorTask $task) : void;

	public function onCompleteFraction(EditorTask $task, int $completed, int $total) : void;

	public function onCompletion(EditorTask $task) : void;
}