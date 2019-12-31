<?php

declare(strict_types=1);

namespace cosmicpe\buildertools\editor;

use cosmicpe\buildertools\editor\task\EditorTask;
use pocketmine\scheduler\Task;

final class EditorTaskHandler extends Task{

	private const OPS_PER_TICK = 65536;

	/** @var EditorTaskInfo[] */
	private $tasks = [];

	public function handle(EditorTask $task) : void{
		$this->tasks[spl_object_id($task)] = EditorTaskInfo::fromEditorTask($task);
	}

	public function onRun(int $currentTick) : void{
		$tasks_c = count($this->tasks);
		if($tasks_c > 0){
			$ops = (int) floor(self::OPS_PER_TICK / count($this->tasks));
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
			}
		}
	}
}