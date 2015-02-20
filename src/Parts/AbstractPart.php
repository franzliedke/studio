<?php

namespace Studio\Parts;

use League\Flysystem\Filesystem;

abstract class AbstractPart implements PartInterface
{

    /**
     * @var PartInputInterface
     */
    protected $input;


    abstract public function setupPackage($config, Filesystem $target);

    public function setInput(PartInputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

}
