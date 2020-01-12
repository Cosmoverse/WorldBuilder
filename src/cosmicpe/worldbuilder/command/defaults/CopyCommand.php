<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\RequireSelectionCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\task\CopyEditorTask;
use cosmicpe\worldbuilder\editor\task\listener\EditorTaskOnCompletionListener;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\clipboard\Clipboard;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\session\utils\Selection;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CopyCommand extends Command{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/copy", "Copies blocks in the selected space");
		$this->addCheck(new RequireSelectionCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		/** @var Player $sender */
		$session = PlayerSessionManager::get($sender);
		$session->setClipboard(null);

		/** @var Selection $selection */
		$selection = $session->getSelection();
		$task = new CopyEditorTask($sender->getWorld(), $selection, new Clipboard($selection, Vector3::minComponents(...$selection->getPoints())->subtract($sender->getPosition()->floor())));
		$task->registerListener(new EditorTaskOnCompletionListener(static function(CopyEditorTask $task) use($session) : void{
			$session->setClipboard($task->getClipboard());
		}));
		$session->pushEditorTask($task, TextFormat::GREEN . "Copying selection");
	}
}