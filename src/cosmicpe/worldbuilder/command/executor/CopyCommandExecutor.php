<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\CopyEditorTaskInfo;
use cosmicpe\worldbuilder\editor\executor\EditorTaskInfo;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskOnCompletionListener;
use cosmicpe\worldbuilder\editor\utils\schematic\SimpleSchematic;
use cosmicpe\worldbuilder\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function assert;

final class CopyCommandExecutor implements CommandExecutor{

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->loader->getPlayerSessionManager()->get($sender);
		$session->clipboard_schematic = null;

		$p1 = $session->selection->getPoint(0);
		$p2 = $session->selection->getPoint(1);
		$instance = $this->loader->getEditorManager()->buildInstance(new CopyEditorTaskInfo(
			$sender->getWorld(),
			$p1->x, $p1->y, $p1->z,
			$p2->x, $p2->y, $p2->z,
			new SimpleSchematic(Vector3::minComponents(...$session->selection->getPoints())->subtractVector($sender->getPosition()->floor()), $p1, $p2),
			$this->loader->getEditorManager()->generate_new_chunks
		));
		$instance->registerListener(new EditorTaskOnCompletionListener(static function(EditorTaskInfo $task) use($session) : void{
			assert($task instanceof CopyEditorTaskInfo);
			$session->clipboard_schematic = $task->clipboard;
		}));
		$session->pushEditorTask($instance, TextFormat::GREEN . "Copying selection");
		return true;
	}
}