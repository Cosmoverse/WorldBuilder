<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\command\executor;

use cosmicpe\worldbuilder\editor\executor\SetSchematicEditorTaskInfo;
use cosmicpe\worldbuilder\editor\format\EditorFormatIds;
use cosmicpe\worldbuilder\Loader;
use cosmicpe\worldbuilder\utils\FileSystemUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use SplFileInfo;

final class SchematicCommandExecutor implements CommandExecutor{

	private const FILE_EXTENSION = "schematic";

	public function __construct(
		readonly private Loader $loader
	){}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(isset($args[0])){
			switch($args[0]){
				case "list":
					$found = 0;
					$message = "";
					$directory = $this->loader->getDataFolder();
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
						$file = new SplFileInfo($this->loader->getDataFolder() . implode(" ", array_slice($args, 1)) . "." . self::FILE_EXTENSION);
						if($file->isFile()){
							$path = $file->getRealPath();
							assert($path !== null);
							$contents = file_get_contents($path);

							assert($sender instanceof Player);

							$schematic = $this->loader->getEditorManager()->format_registry->get(EditorFormatIds::MINECRAFT_SCHEMATIC)->import($contents);
							$relative = $sender->getPosition();
							$manager = $this->loader->getEditorManager();
							$this->loader->getPlayerSessionManager()->get($sender)->pushEditorTask($manager->buildInstance(new SetSchematicEditorTaskInfo(
								$sender->getWorld(),
								$schematic,
								$relative->getFloorX(),
								$relative->getFloorY(),
								$relative->getFloorZ(),
								$manager->generate_new_chunks
							)), TextFormat::GREEN . "Importing {$file->getFilename()}");
						}else{
							$sender->sendMessage(TextFormat::RED . "File not found: {$file->getPathname()}");
						}
					}else{
						$sender->sendMessage(TextFormat::RED . "/{$label} import <file_name>");
					}
					return true;
				case "export":
					if(isset($args[1])){
						$export_path = $this->loader->getDataFolder() . implode(" ", array_slice($args, 1)) . "." . self::FILE_EXTENSION;
						if(!file_exists($export_path)){
							assert($sender instanceof Player);
							$session = $this->loader->getPlayerSessionManager()->get($sender);
							$schematic = $session->clipboard_schematic;
							if($schematic !== null){
								$contents = $this->loader->getEditorManager()->format_registry->get(EditorFormatIds::MINECRAFT_SCHEMATIC)->export($schematic);
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