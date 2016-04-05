<?php

namespace Studio\Config;

interface Storage
{
    public function readPaths();

    public function writePaths($paths);
}
