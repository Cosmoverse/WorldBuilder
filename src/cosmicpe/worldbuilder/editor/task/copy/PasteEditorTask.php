<?php

declare(strict_types=1);

namespace cosmicpe\worldbuilder\editor\task\copy;

use cosmicpe\worldbuilder\editor\task\SetSchematicEditorTask;

class PasteEditorTask extends SetSchematicEditorTask{

	public function getName() : string{
		return "paste";
	}
}