<?php

namespace Studio\Parts;

use League\Flysystem\Filesystem;

interface PartInterface
{

    public function setupPackage($config, Filesystem $target);

}
