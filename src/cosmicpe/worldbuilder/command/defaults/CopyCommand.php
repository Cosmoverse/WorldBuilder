<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\copy\CopyEditorTask;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskOnCompletionListener;
use cosmicpe\worldbuilder\editor\utils\schematic\SimpleSchematic;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CopyCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/copy", "Copies blocks in the selected space");
		$this->setPermission("worldbuilder.command.copy");
		$this->addCheck(new RequireSelectionCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		assert($sender instanceof Player);
		$session = PlayerSessionManager::get($sender);
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
	}
}