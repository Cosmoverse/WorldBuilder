<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\task\copy\CopyEditorTask;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskOnCompletionListener;
use cosmicpe\worldbuilder\editor\utils\schematic\SimpleSchematic;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class CopyCommandExecutor extends WorldBuilderCommandExecutor{

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		assert($sender instanceof Player);
		$session = $this->getLoader()->getPlayerSessionManager()->get($sender);
		$session->setClipboardSchematic(null);

		/** @var Selection $selection */
		$selection = $session->getSelection();
		$task = new CopyEditorTask($sender->getWorld(), $selection, new SimpleSchematic(
			Vector3::minComponents(...$selection->getPoints())->subtractVector($sender->getPosition()->floor()),
			$selection->getPoint(0),
			$selection->getPoint(1)
		));
		$task->registerListener(new EditorTaskOnCompletionListener(static function(CopyEditorTask $task) use($session) : void{
			$session->setClipboardSchematic($task->getClipboard());
		}));
		$session->pushEditorTask($task, TextFormat::GREEN . "Copying selection");
		return true;
	}
}