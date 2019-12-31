# WorldBuilder
A world editor plugin designed for production use.

WorldBuilder is specifically designed to handle multiple world edit operations efficiently.
Unlike most of the async world editor plugins, WorldBuilder while being 100% asynchronous, does not execute world edit tasks on a new thread.
It instead executes them on the main thread but splits the task into several smaller tasks which are executed over several game ticks.
There is a configurable limit on how many block iterations can occur over a tick which acts as a performance regulator.

**NOTE:** WorldBuilder is an efficiency-first, annoyance-last world editor plugin so do not expect light-speed world edits.
There is a default limit of 65536 block iterations per tick (or about 1,310,720 per second) so if you're editing 78,643,200 million
blocks volume region on an "INFINITE frequency" CPU, the task will always take about a minute to complete.

WorldBuilder is great for creative mode servers and provides an API for limiting and monitoring world edits.


## Developer Docs
### Handling and filtering editor tasks
If you are running a creative-mode server, you may want to disallow players from editing other player's region, or perhaps blacklisting TNTs and falling blocks from being world-edited in.
```php
public function onPlayerTriggerEditorTask(PlayerTriggerEditorTaskEvent $event) : void{
	$player = $event->getPlayer();
	$task = $event->getTask();

	$world = $event->getWorld();
	// TODO: Check if $world is not spawn_world

	$selection = $event->getSelection();
	// TODO: Check if all $selection->getPoints() lie in $player's plot.

	if($task instanceof SetEditorTask){
		$block = $task->getBlock();
		if($block->getId() === BlockLegacyIds::TNT){
			$event->setCancelled();
			$player->sendMessage("//set-ting " . $block->getName() . " is not allowed!");
		}
	}else{
		// disallow players from using anything else besides //set because i am a dick.
		$event->setCancelled();
		$player->sendMessage($task->getName() . " is disallowed on the server.");
	}
}
```

### Monitoring editor tasks
Boy do I need some sick world editor progress bars on my server! Let's start by creating an editor task listener...
```php
class MahEditorTaskListener implements EditorTaskListener{

	public function onRegister(EditorTask $task) : void{
		// Called after PlayerTriggerEditorTaskEvent has been triggered and the
		// event hasn't been cancelled.
	}
	
	public function onCompleteFraction(EditorTask $task, int $completed, int $total) : void{
		// Progress bar material here!
		// $player->sendPopup($task->getName() . " completed: " . sprintf("%0.2f", ($completed / $total) * 100) . "%");
	}
	
	public function onCompletion(EditorTask $task) : void{
		// $player->sendPopup($task->getName() . " completed!");
	}
}
```
...and then register it to an editor task!
```php
/**
 * @param PlayerTriggerEditorTaskEvent $event
 * @priority MONITOR
 */
public function onPlayerTriggerEditorTask(PlayerTriggerEditorTaskEvent $event) : void{
	$task = $event->getTask();
	$handler = $task->registerListener(new MahEditorTaskListener());
	// Don't forget to call $handler->unregister() once the player quits the server, if
	// your listener is indirectly sending packets to the player.
	// It is not necessary to unregister a handler from a completed task, you can safely
	// register and forget.
}
```
