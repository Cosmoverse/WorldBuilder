<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use Closure;
use cosmicpe\worldbuilder\editor\format\EditorFormatRegistry;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\EditorTask;
use cosmicpe\worldbuilder\Loader;
use Generator;
use pocketmine\scheduler\ClosureTask;
use RuntimeException;
use SOFe\AwaitGenerator\Await;
use function array_keys;
use function array_rand;
use function count;
use function floor;
use function max;
use function shuffle;
use function spl_object_id;

final class EditorManager{

	readonly public EditorFormatRegistry $format_registry;
	private int $max_ops_per_tick;
	private bool $running = false;

	/** @var array<int, EditorTaskInfo> */
	private array $tasks = [];

	/** @var list<Closure() : void> */
	private array $sleeping = [];

	public function __construct(){
		NamedtagCopierManager::init();
		$this->format_registry = new EditorFormatRegistry();
	}

	public function init(Loader $plugin) : void{
		$this->max_ops_per_tick = (int) $plugin->getConfig()->get("max-ops-per-tick");
		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			$sleeping = $this->sleeping;
			$this->sleeping = [];
			foreach($sleeping as $callback){
				$callback();
			}
		}), 1);
	}

	public function push(EditorTask $task) : void{
		$this->tasks[spl_object_id($task)] = EditorTaskInfo::fromEditorTask($task);
		if(!$this->running){
			Await::g2c($this->schedule());
		}
	}

	/**
	 * @return Generator<mixed, Await::RESOLVE, void, void>
	 */
	private function sleep() : Generator{
		return Await::promise(function(Closure $resolve) : void{ $this->sleeping[] = $resolve; });
	}

	private function schedule() : Generator{
		!$this->running || throw new RuntimeException("Tried to run a duplicate scheduler");
		$this->running = true;
		$tasks_c = count($this->tasks);
		$tasks_c > 0 || throw new RuntimeException("Tried to run a scheduler without pending tasks");
		$ops = max(1024, (int) floor($this->max_ops_per_tick / $tasks_c));
		$completed = 0;
		$ids = array_keys($this->tasks);
		shuffle($ids);
		while(count($this->tasks) > 0){
			$id = array_rand($this->tasks);
			$task = $this->tasks[$id];
			$limit = $ops;
			while(true){
				if(!(yield from $task->generator->next($null))){
					unset($this->tasks[$id]);
					$task->task->onCompletion();
					break;
				}
				if(--$limit === 0){
					break;
				}
			}
			if(isset($this->tasks[$id])){
				$task->task->onCompleteOperations($ops - $limit);
			}
			$completed += $ops - $limit;
			if($completed >= $ops){
				yield from $this->sleep();
				$completed = 0;
			}
		}
		$this->running = false;
	}
}