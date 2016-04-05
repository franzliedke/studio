<?php

namespace Studio\Config;

class Version1Serializer implements Serializer
{
    public function deserializePaths($obj)
    {
        return array_values($obj['packages']);
    }

    public function serializePaths($paths)
    {
        return ['packages' => $paths];
    }
}
