<?php

namespace Studio\Config;

interface StorageInterface
{
    public function store($packages);

    public function load();
}