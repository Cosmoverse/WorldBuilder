<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

use cosmicpe\worldbuilder\editor\task\listener\ClosureEditorTaskListenerInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskListener;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskListenerInfo;
use cosmicpe\worldbuilder\session\utils\Selection;
use Generator;
use InvalidArgumentException;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\World;

abstract class EditorTask{

	/** @var Selection */
	protected $selection;

	/** @var SubChunkExplorer */
	protected $iterator;

	/** @var EditorTaskListener[] */
	private $listeners = [];

	/** @var int */
	private $operations_completed = 0;

	/** @var int */
	private $estimated_operations;

	public function __construct(World $world, Selection $selection, int $estimated_operations){
		$this->selection = $selection;
		$this->iterator = new SubChunkExplorer($world);
		$this->estimated_operations = $estimated_operations;
	}

	final public function registerListener(EditorTaskListener $listener) : EditorTaskListenerInfo{
		$this->listeners[$spl_id = spl_object_id($listener)] = $listener;
		$listener->onRegister($this);
		return new ClosureEditorTaskListenerInfo(function() use($spl_id) : void{ unset($this->listeners[$spl_id]); });
	}

	final public function getWorld() : World{
		$world = $this->iterator->world;
		if(!($world instanceof World)){
			throw new InvalidArgumentException("Expected supplied tworld to be an instance of " . World::class . ", got " . get_class($world));
		}
		return $world;
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

	protected function onChunkChanged(int $chunkX, int $chunkZ) : void{
		/** @var World $world */
		$world = $this->iterator->world;
		$chunk = $world->getOrLoadChunk($chunkX, $chunkZ, false);
		if($chunk !== null){
			$world->setChunk($chunkX, $chunkZ, $chunk, false);
		}
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