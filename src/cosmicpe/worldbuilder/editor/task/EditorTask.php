<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\listener\ClosureEditorTaskListenerInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskListener;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskListenerInfo;
use cosmicpe\worldbuilder\editor\task\utils\ChunkIteratorCursor;
use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use pocketmine\world\World;

abstract class EditorTask{

	protected Selection $selection;
	protected World $world;
	private int $operations_completed = 0;
	private int $estimated_operations;

	/** @var EditorTaskListener[] */
	private array $listeners = [];

	public function __construct(World $world, Selection $selection, int $estimated_operations){
		$this->selection = $selection;
		$this->world = $world;
		$this->estimated_operations = $estimated_operations;
	}

	final public function registerListener(EditorTaskListener $listener) : EditorTaskListenerInfo{
		$this->listeners[$spl_id = spl_object_id($listener)] = $listener;
		$listener->onRegister($this);
		return new ClosureEditorTaskListenerInfo(function() use($spl_id) : void{ unset($this->listeners[$spl_id]); });
	}

	final public function getWorld() : World{
		return $this->world;
	}

	final public function getSelection() : Selection{
		return $this->selection;
	}

	final public function getEstimatedOperations() : int{
		return $this->estimated_operations;
	}

	abstract public function getName() : string;

	/**
	 * @return Generator<bool>
	 */
	abstract public function run() : Generator;

	protected function onChunkChanged(ChunkIteratorCursor $cursor) : void{
		$cursor->world->setChunk($cursor->chunkX, $cursor->chunkZ, $cursor->chunk, false);
	}

	public function onCompleteOperations(int $completed) : void{
		$this->operations_completed += $completed;
		foreach($this->listeners as $listener){
			$listener->onCompleteFraction($this, $this->operations_completed, $this->estimated_operations);
		}
	}

	public function onCompletion() : void{
		foreach($this->listeners as $listener){
			$listener->onCompletion($this);
		}
	}
}