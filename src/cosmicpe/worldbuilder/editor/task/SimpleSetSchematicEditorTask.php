<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task;

class SimpleSetSchematicEditorTask extends SetSchematicEditorTask{

	public function getName() : string{
		return "paste_schematic";
	}
}