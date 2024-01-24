<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use Closure;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use function count;

final class EditorTaskHandler extends Task{

	/** @var array<int, EditorTaskInfo> */
	private array $tasks = [];

	/**
	 * @param int $max_operations_per_tick
	 * @param Closure() : void $on_tasks_completion
	 */
	public function __construct(
		readonly private int $max_operations_per_tick,
		readonly private Closure $on_tasks_completion
	){}

	public function handle(EditorTask $task) : void{
		$this->tasks[spl_object_id($task)] = EditorTaskInfo::fromEditorTask($task);
	}

	public function onRun() : void{
		$tasks_c = count($this->tasks);
		$ops = max(1024, (int) floor($this->max_operations_per_tick / $tasks_c));
		$completed = 0;
		$ids = array_keys($this->tasks);
		shuffle($ids);
		foreach($ids as $id){
			$limit = $ops;
			$initial = $limit;
			$info = $this->tasks[$id];
			$generator = $info->generator;
			while(--$limit >= 0){
				if(!$generator->send(true) || !$generator->valid()){
					unset($this->tasks[$id]);
					if(--$tasks_c > 0){
						$ops += (int) floor($limit / count($this->tasks));
					}
					$info->task->onCompletion();

					if(count($this->tasks) === 0){
						($this->on_tasks_completion)();
						throw new CancelTaskException("No tasks left");
					}
					break;
				}
			}
			if(isset($this->tasks[$id])){
				$info->task->onCompleteOperations($initial - $limit);
			}
			$completed += $initial - $limit;
			if($completed >= $ops){
				break;
			}
		}
	}
}

