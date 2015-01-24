<?php

namespace Studio\Config;

class FileStorage implements StorageInterface
{
    protected $file;


    public function __construct($file)
    {
        $this->file = $file;

        $this->ensureFile();
    }

    public function store($packages)
    {
        $this->writeToFile(['packages' => $packages]);
    }

    public function load()
    {
        $contents = json_decode(file_get_contents($this->file), true);
        return $contents['packages'];
    }

    protected function ensureFile()
    {
        if (file_exists($this->file)) return;

        // If the config file does not exist, we simply create one
        // with an empty list of packages.
        $this->store([]);
    }

    protected function writeToFile(array $data)
    {
        file_put_contents($this->file, json_encode($data));
    }
}
