<?php

namespace Studio\Parts;

use League\Flysystem\Filesystem;

abstract class AbstractPart implements PartInterface
{

    /**
     * @var PartInputInterface
     */
    protected $input;


    abstract public function setupPackage($composer, Filesystem $target);

    public function setInput(PartInputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    protected function copyTo($file, Filesystem $target, $targetName = null)
    {
        $targetName = $targetName ?: basename($file);

        $target->write($targetName, file_get_contents($file));
    }

}
