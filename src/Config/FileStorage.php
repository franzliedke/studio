<?php

namespace Studio\Config;

class FileStorage implements StorageInterface
{
    protected $file;


    public function __construct($file)
    {
        $this->file = $file;
    }

    public function store($packages)
    {
        $this->writeToFile(['packages' => $packages]);
    }

    public function load()
    {
        if (file_exists($this->file)) {
            $contents = json_decode(file_get_contents($this->file), true);
            return $contents['packages'];
        }

        return [];
    }

    protected function writeToFile(array $data)
    {
        file_put_contents($this->file, json_encode($data));
    }
}
