<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use cosmicpe\worldbuilder\editor\task\EditorTask;
use pocketmine\scheduler\Task;

final class EditorTaskHandler extends Task{

	/** @var int */
	private $max_operations_per_tick;

	/** @var EditorTaskInfo[] */
	private $tasks = [];

	public function __construct(int $max_operations_per_tick){
		$this->max_operations_per_tick = $max_operations_per_tick;
	}

	public function handle(EditorTask $task) : void{
		$this->tasks[spl_object_id($task)] = EditorTaskInfo::fromEditorTask($task);
	}

	public function onRun(int $currentTick) : void{
		$tasks_c = count($this->tasks);
		if($tasks_c > 0){
			$ops = max(1024, (int) floor($this->max_operations_per_tick / count($this->tasks)));
			$completed = 0;
			$ids = array_keys($this->tasks);
			shuffle($ids);
			foreach($ids as $id){
				$limit = $ops;
				$initial = $limit;
				$info = $this->tasks[$id];
				$generator = $info->getGenerator();
				while(--$limit >= 0){
					if(!$generator->send(true) || !$generator->valid()){
						unset($this->tasks[$id]);
						if(--$tasks_c > 0){
							$ops += (int) floor($limit / count($this->tasks));
						}
						$info->getTask()->onCompletion();
						break;
					}
				}
				if(isset($this->tasks[$id])){
					$info->getTask()->onCompleteOperations($initial - $limit);
				}
				$completed += $initial - $limit;
				if($completed >= $ops){
					break;
				}
			}
		}
	}
}