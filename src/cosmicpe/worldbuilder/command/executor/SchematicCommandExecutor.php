<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\format\EditorFormatIds;
use cosmicpe\worldbuilder\editor\task\SimpleSetSchematicEditorTask;
use cosmicpe\worldbuilder\utils\FileSystemUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use SplFileInfo;

final class SchematicCommandExecutor extends WorldBuilderCommandExecutor{

	private const FILE_EXTENSION = "schematic";

	protected function executeCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(isset($args[0])){
			switch($args[0]){
				case "list":
					$found = 0;
					$message = "";
					$directory = $this->getLoader()->getDataFolder();
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
					return true;
				case "import":
					if(isset($args[1])){
						$file = new SplFileInfo($this->getLoader()->getDataFolder() . implode(" ", array_slice($args, 1)) . "." . self::FILE_EXTENSION);
						if($file->isFile()){
							$path = $file->getRealPath();
							assert($path !== null);
							$contents = file_get_contents($path);

							assert($sender instanceof Player);

							$schematic = $this->getLoader()->getEditorManager()->getFormatRegistry()->get(EditorFormatIds::MINECRAFT_SCHEMATIC)->read($contents);
							$this->getLoader()->getPlayerSessionManager()->get($sender)->pushEditorTask(new SimpleSetSchematicEditorTask($sender->getWorld(), $schematic, $sender->getPosition()->floor()), TextFormat::GREEN . "Importing {$file->getFilename()}");
						}else{
							$sender->sendMessage(TextFormat::RED . "File not found: {$file->getPathname()}");
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "/{$label} import <file_name>");
					}
					return true;
				case "export":
					$sender->sendMessage(TextFormat::RED . "Exporting schematics is currently not supported");
					return true;
			}
		}

		$sender->sendMessage(
			TextFormat::RED . "/{$label} list" . TextFormat::GRAY . " - Lists all importable schematics" . TextFormat::EOL .
			TextFormat::RED . "/{$label} import <file_name>" . TextFormat::GRAY . " - Import structure from a <file_name>.schematic" . TextFormat::EOL .
			TextFormat::RED . "/{$label} export <file_name>" . TextFormat::GRAY . " - Export selected area as <file_name>.schematic"
		);
		return true;
	}
}