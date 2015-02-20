<?php

namespace Studio\Parts;

use League\Flysystem\Filesystem;

interface PartInterface
{

    public function setupPackage($composer, Filesystem $target);

}
