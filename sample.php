<?php

use Amantosh\DuplicateFileRemover as Remove;

require "class.duplicateFileRemover.php";

$sample_path = "path/to/a/folder/";


$ins = new Remove($sample_path);

$ins->start_process();