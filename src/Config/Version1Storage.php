<?php

namespace Studio\Config;

class Version1Storage implements Storage
{
    protected $file;


    public function __construct($file)
    {
        $this->file = $file;
    }

    public function readPaths()
    {
        return array_values($this->load());
    }

    public function writePaths($paths)
    {
        $this->store($paths);
    }

    protected function store($packages)
    {
        $this->writeToFile(['packages' => $packages]);
    }

    protected function load()
    {
        if (!file_exists($this->file)) return [];

        $contents = $this->readFromFile();
        return $contents['packages'];
    }

    protected function writeToFile(array $data)
    {
        file_put_contents(
            $this->file,
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )."\n"
        );
    }

    protected function readFromFile()
    {
        return json_decode(file_get_contents($this->file), true);
    }
}
