<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor;

use Closure;
use cosmicpe\worldbuilder\editor\executor\DefaultEditorTaskExecutor;
use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\RegenerateChunksEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\ReplaceEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\ReplaceSetRandomEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\SetBiomeEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\SetEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\SetRandomEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\SetSchematicEditorTaskInfo;
use cosmicpe\worldbuilder\editor\format\EditorFormatRegistry;
use cosmicpe\worldbuilder\editor\task\copy\nbtcopier\NamedtagCopierManager;
use cosmicpe\worldbuilder\editor\task\listener\PopupProgressEditorTaskListener;
use cosmicpe\worldbuilder\event\player\PlayerTriggerEditorTaskEvent;
use cosmicpe\worldbuilder\Loader;
use Generator;
use pocketmine\event\EventPriority;
use pocketmine\scheduler\ClosureTask;
use RuntimeException;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitGenerator\Traverser;
use function array_keys;
use function array_rand;
use function count;
use function floor;
use function max;
use function shuffle;
use function spl_object_id;

final class EditorManager{

	readonly public EditorFormatRegistry $format_registry;
	readonly private DefaultEditorTaskExecutor $default_editor_task_executor;
	public bool $generate_new_chunks = true;
	private int $max_ops_per_tick;
	private bool $running = false;

	/** @var array<int, EditorTaskInstance> */
	private array $tasks = [];

	/** @var list<Closure() : void> */
	private array $sleeping = [];

	/**
	 * @var array<class-string, Closure(object) : Generator<array{int, int}, Traverser::VALUE>>
	 */
	public array $editor_task_info_handlers;

	public function __construct(){
		NamedtagCopierManager::init();
		$this->format_registry = new EditorFormatRegistry();
		$this->default_editor_task_executor = new DefaultEditorTaskExecutor();
		$this->editor_task_info_handlers = [
			RegenerateChunksEditorTaskInfo::class => $this->default_editor_task_executor->regenerateChunks(...),
			ReplaceEditorTaskInfo::class => $this->default_editor_task_executor->replace(...),
			ReplaceSetRandomEditorTaskInfo::class => $this->default_editor_task_executor->replaceSetRandom(...),
			SetBiomeEditorTaskInfo::class => $this->default_editor_task_executor->setBiome(...),
			SetEditorTaskInfo::class => $this->default_editor_task_executor->set(...),
			SetRandomEditorTaskInfo::class => $this->default_editor_task_executor->setRandom(...),
			SetSchematicEditorTaskInfo::class => $this->default_editor_task_executor->setSchematic(...)
		];
	}

	public function init(Loader $plugin) : void{
		$this->generate_new_chunks = (bool) $plugin->getConfig()->get("generate-new-chunks", true);
		$this->max_ops_per_tick = (int) $plugin->getConfig()->get("max-ops-per-tick");
		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			$sleeping = $this->sleeping;
			$this->sleeping = [];
			foreach($sleeping as $callback){
				$callback();
			}
		}), 1);

		if($plugin->getConfig()->get("display-progress-bar", true)){
			$plugin->getServer()->getPluginManager()->registerEvent(PlayerTriggerEditorTaskEvent::class, function(PlayerTriggerEditorTaskEvent $event) : void{
				$event->instance->registerListener(new PopupProgressEditorTaskListener($event->player));
			}, EventPriority::MONITOR, $plugin);
		}
	}

	public function buildInstance(EditorTaskInfo $info) : EditorTaskInstance{
		return new EditorTaskInstance($info, new Traverser($this->editor_task_info_handlers[$info::class]($info)));
	}

	public function push(EditorTaskInstance $instance) : void{
		$this->tasks[spl_object_id($instance)] = $instance;
		if(!$this->running){
			Await::g2c($this->schedule());
		}
	}

	/**
	 * @return Generator<void, Await::RESOLVE, void, void>
	 */
	private function sleep() : Generator{
		/** @var Closure(Closure() : void) : void $closure */
		$closure = function(Closure $resolve) : void{ $this->sleeping[] = $resolve; };
		yield from Await::promise($closure);
	}

	private function calculateOpsPerTick() : int{
		return max(1024, (int) floor($this->max_ops_per_tick / max(count($this->tasks), 1)));
	}

	private function schedule() : Generator{
		!$this->running || throw new RuntimeException("Tried to run a duplicate scheduler");
		$this->running = true;
		$completed = 0;
		$ops = $this->calculateOpsPerTick();
		while(count($this->tasks) > 0){
			$id = array_rand($this->tasks);
			$state = $this->tasks[$id];
			$limit = $ops;
			$progress = null;
			while(true){
				if(!(yield from $state->generator->next($progress))){
					unset($this->tasks[$id]);
					$state->onCompletion();
					$progress = null;
					break;
				}
				if(--$limit === 0){
					break;
				}
			}
			if($progress !== null){
				$state->onCompleteOperations($progress[0], $progress[1]);
			}
			$completed += $ops - $limit;
			if($completed >= $ops){
				yield from $this->sleep();
				$completed = 0;
				$ops = $this->calculateOpsPerTick();
			}
		}
		$this->running = false;
	}
}