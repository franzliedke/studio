<?php

namespace Studio\Parts;

use Closure;
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

    protected function copyTo($file, Filesystem $target, $targetName = null, Closure $handler = null)
    {
        $targetName = $targetName ?: basename($file);

        $content = file_get_contents($file);

        if ($handler) {
            $content = $handler($content);
        }

        $target->write($targetName, $content);
    }

}
