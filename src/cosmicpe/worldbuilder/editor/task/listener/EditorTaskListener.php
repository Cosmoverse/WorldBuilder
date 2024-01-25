<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\listener;

use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;

interface EditorTaskListener{

	public function onRegister(EditorTaskInfo $info) : void;

	public function onCompleteFraction(EditorTaskInfo $info, int $completed, int $total) : void;

	public function onCompletion(EditorTaskInfo $info) : void;
}