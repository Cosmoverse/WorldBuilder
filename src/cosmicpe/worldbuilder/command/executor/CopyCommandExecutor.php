<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\CopyEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskOnCompletionListener;
use cosmicpe\worldbuilder\editor\utils\clipboard\BufferedClipboard;
use cosmicpe\worldbuilder\editor\utils\clipboard\InMemoryClipboard;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use RuntimeException;
use function assert;
use function tmpfile;

final class CopyCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader,
		readonly private bool $buffered
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->loader->getPlayerSessionManager()->get($sender);
		$session->clipboard = null;

		$p1 = $session->selection->getPoint(0);
		$p2 = $session->selection->getPoint(1);
		$relative_pos = Vector3::minComponents($p1, $p2)->subtractVector($sender->getPosition()->floor());
		if($this->buffered){
			$resource = tmpfile();
			$resource !== false || throw new RuntimeException("Failed to create temporary resource file");
			$this->loader->getLogger()->debug("Created temporary resource file for clipboard: " . stream_get_meta_data($resource)["uri"]);
			$clipboard = new BufferedClipboard($relative_pos, $p1, $p2, $resource);
			// resource will automatically be deleted when clipboard is gc-d
		}else{
			$clipboard = new InMemoryClipboard($relative_pos, $p1, $p2);
		}
		$instance = $this->loader->getEditorManager()->buildInstance(new CopyEditorTaskInfo(
			$sender->getWorld(),
			$p1->x, $p1->y, $p1->z,
			$p2->x, $p2->y, $p2->z,
			$clipboard,
			$this->loader->getEditorManager()->generate_new_chunks
		));
		$instance->registerListener(new EditorTaskOnCompletionListener(static function(EditorTaskInfo $task) use($session) : void{
			assert($task instanceof CopyEditorTaskInfo);
			$session->clipboard = $task->clipboard;
		}));
		$session->pushEditorTask($instance, TextFormat::GREEN . "Copying selection");
		return true;
	}
}