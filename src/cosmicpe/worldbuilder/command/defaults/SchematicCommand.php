<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\defaults;

use cosmicpe\worldbuilder\command\check\PlayerOnlyCommandCheck;
use cosmicpe\worldbuilder\command\Command;
use cosmicpe\worldbuilder\editor\EditorManager;
use cosmicpe\worldbuilder\editor\format\EditorFormatIds;
use cosmicpe\worldbuilder\editor\task\SimpleSetSchematicEditorTask;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\session\PlayerSessionManager;
use cosmicpe\worldbuilder\utils\FileSystemUtils;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use SplFileInfo;

class SchematicCommand extends Command{

	private const FILE_EXTENSION = "schematic";

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "/schematic", "Pastes blocks in the selected space", ["/schem"]);
		$this->setPermission("worldbuilder.command.schematic");
		$this->addCheck(new PlayerOnlyCommandCheck());
	}

	public function onExecute(CommandSender $sender, string $label, array $args) : void{
		if(isset($args[0])){
			switch($args[0]){
				case "list":
					$found = 0;
					$message = "";
					$directory = $this->getPlugin()->getDataFolder();
					foreach(FileSystemUtils::findFilesWithExtension($directory, self::FILE_EXTENSION) as $file){
						$message .= TextFormat::RED . TextFormat::BOLD . ++$found . ". " . TextFormat::RESET . TextFormat::RED . $file->getBasename("." . self::FILE_EXTENSION) . TextFormat::GRAY . " [" . FileSystemUtils::printBytesToHumanReadable($file->getSize()) . "]" . TextFormat::EOL;
					}
					if($found > 0){
						$sender->sendMessage(
							TextFormat::RED . "Found " . TextFormat::BOLD . $found . TextFormat::RESET . TextFormat::RED . " .schematic file(s):" . TextFormat::EOL .
							$message
						);
					}else{
						$sender->sendMessage(TextFormat::RED . "No .schematic files were found in {$directory}.");
					}
					return;
				case "import":
					if(isset($args[1])){
						$file = new SplFileInfo($this->getPlugin()->getDataFolder() . implode(" ", array_slice($args, 1)) . "." . self::FILE_EXTENSION);
						if($file->isFile()){
							$path = $file->getRealPath();
							assert($path !== null);
							$contents = file_get_contents($path);

							assert($sender instanceof Player);

							$schematic = EditorManager::getFormatRegistry()->get(EditorFormatIds::MINECRAFT_SCHEMATIC)->import($contents);
							PlayerSessionManager::get($sender)->pushEditorTask(new SimpleSetSchematicEditorTask($sender->getWorld(), $schematic, $sender->getPosition()->floor()), TextFormat::GREEN . "Importing {$file->getFilename()}");
						}else{
							$sender->sendMessage(TextFormat::RED . "File not found: {$file->getPathname()}");
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "/{$label} import <file_name>");
					}
					return;
				case "export":
					if(isset($args[1])){
						$export_path = $this->getPlugin()->getDataFolder() . implode(" ", array_slice($args, 1)) . "." . self::FILE_EXTENSION;
						if(!file_exists($export_path)){
							assert($sender instanceof Player);
							$session = PlayerSessionManager::get($sender);
							$schematic = $session->getClipboardSchematic();
							if($schematic !== null){
								$contents = EditorManager::getFormatRegistry()->get(EditorFormatIds::MINECRAFT_SCHEMATIC)->export($schematic);
								file_put_contents($export_path, $contents);
								$sender->sendMessage(TextFormat::GREEN . "Exported clipboard to {$export_path}.");
							}else{
								$sender->sendMessage(TextFormat::RED . "You must //copy the region you'd like to export.");
							}
						}else{
							$sender->sendMessage(TextFormat::RED . "Cannot overwrite existing file or directory {$export_path}.");
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "/{$label} export <file_name>");
					}
					return;
			}
		}

		$sender->sendMessage(
			TextFormat::RED . "/{$label} list" . TextFormat::GRAY . " - Lists all importable schematics" . TextFormat::EOL .
			TextFormat::RED . "/{$label} import <file_name>" . TextFormat::GRAY . " - Import structure from a <file_name>.schematic" . TextFormat::EOL .
			TextFormat::RED . "/{$label} export <file_name>" . TextFormat::GRAY . " - Export selected area as <file_name>.schematic"
		);
	}
}