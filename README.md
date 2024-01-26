# WorldBuilder
A world editor plugin designed for production use.

WorldBuilder (WB) is specifically designed to handle multiple world-edit operations efficiently.
It is an excellent editor plugin creative-mode like servers (such as MyPlot plugin users).
WB is _not_ a fast editor—instead, it is deliberately slows down editor tasks to let the server handle multiple operations concurrently.

WB provides an API for limiting and monitoring world edit operations.

## Developer Docs
### Handling and filtering editor tasks
As a creative-mode server maintainer, disallowing players from editing other's regions and avoiding specific blocks from being spawned can be done using WB's API in the following way:

```php
use cosmicpe\worldbuilder\editor\executor\SetEditorTaskInfo;
use cosmicpe\worldbuilder\event\player\PlayerTriggerEditorTaskEvent;
use pocketmine\block\BlockTypeIds;use pocketmine\math\Vector3;

public function onPlayerTriggerEditorTask(PlayerTriggerEditorTaskEvent $event) : void{
	$player = $event->player;
	$task = $event->instance->info;
	if($task instanceof SetEditorTaskInfo){
		if($task->block->getTypeId() === BlockTypeIds::TNT){
			$event->cancel();
			$player->sendMessage("Setting " . $task->block->getName() . " is not allowed!");
		}
		
		// allow modifying sections between these 2 points
		$p1 = new Vector3(0, 0, 0);
		$p2 = new Vector3(100, 255, 100);
		if($task->x1 < $p1->x || $task->x2 > $p2->x || $task->y1 < $p1->y || $task->y2 > $p2->y || $task->z1 < $p1->z || $task->z2 > $p2->z){
			$event->cancel();
			$player->sendMessage("You cannot build outside your allocated region!");
		}
	}else{
		// disallow players from using anything else besides //set
		$event->cancel();
		$player->sendMessage("This operation is not allowed on the server.");
	}
}
```

### Monitoring editor tasks
```php
use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskListener;

class MyEditorTaskListener implements EditorTaskListener{

	public function onRegister(EditorTaskInfo $info) : void{
		// Called after PlayerTriggerEditorTaskEvent has been triggered and the
		// event hasn't been cancelled.
	}

	public function onCompleteFraction(EditorTaskInfo $info, int $completed, int $total) : void{
		// $player->sendPopup($task->getName() . " completed: " . sprintf("%0.2f", ($completed / $total) * 100) . "%");
	}

	public function onCompletion(EditorTaskInfo $info) : void{
		// $player->sendPopup($task->getName() . " completed!");
	}
}
```
...and then register it to an editor task!

```php
use cosmicpe\worldbuilder\event\player\PlayerTriggerEditorTaskEvent;

/**
 * @param PlayerTriggerEditorTaskEvent $event
 * @priority MONITOR
 */
public function onPlayerTriggerEditorTask(PlayerTriggerEditorTaskEvent $event) : void{
	$event->instance->registerListener(new MyEditorTaskListener());
}
```

### Executing edit operations
An edit operation (e.g., `//copy`, `//paste`, etc.) may be programmatically executed by building an `EditorTaskInfo` object and executing it.
An `EditorTaskInfo` implementation exists for every editor operation.

```php
use cosmicpe\worldbuilder\editor\executor\SetEditorTaskInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskOnCompletionListener;
use cosmicpe\worldbuilder\Loader;
use pocketmine\block\VanillaBlocks;
use pocketmine\Server;

$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
$task = new SetEditorTaskInfo(
	world: $world,
	x1: 0,
	y1: 0,
	z1: 0,
	x2: 100,
	y2: 255,
	z2: 100,
	block: VanillaBlocks::STONE(), // fill this area with stone
	generate_new_chunks: true
);

/** @var Loader $plugin */
$plugin = Server::getInstance()->getPluginManager()->getPlugin("WorldBuilder");

echo "Task started";
$instance = $plugin->getEditorManager()->buildInstance($task);
$instance->registerListener(new EditorTaskOnCompletionListener(function(SetEditorTaskInfo $info) : void{
	echo "Task completed";
}));
$plugin->getEditorManager()->push($instance);
```