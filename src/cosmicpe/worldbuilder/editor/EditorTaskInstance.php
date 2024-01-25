<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskListener;
use SOFe\AwaitGenerator\Traverser;
use function spl_object_id;

final class EditorTaskInstance{

	private int $operations_completed = 0;

	/** @var array<int, EditorTaskListener> */
	private array $listeners = [];

	/**
	 * @param EditorTaskInfo $info
	 * @param Traverser<array{int, int}> $generator
	 */
	public function __construct(
		readonly public EditorTaskInfo $info,
		readonly public Traverser $generator
	){}

	public function registerListener(EditorTaskListener $listener) : void{
		$this->listeners[spl_object_id($listener)] = $listener;
	}

	public function unregisterListener(EditorTaskListener $listener) : void{
		unset($this->listeners[spl_object_id($listener)]);
	}

	public function onRegister() : void{
		foreach($this->listeners as $listener){
			$listener->onRegister($this->info);
		}
	}

	public function onCompleteOperations(int $completed, int $total) : void{
		$this->operations_completed += $completed;
		foreach($this->listeners as $listener){
			$listener->onCompleteFraction($this->info, $completed, $total);
		}
	}

	public function onCompletion() : void{
		foreach($this->listeners as $listener){
			$listener->onCompletion($this->info);
		}
	}
}