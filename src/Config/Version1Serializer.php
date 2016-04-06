<?php

namespace Studio\Config;

class Version1Serializer implements Serializer
{
    public function deserializePaths($obj)
    {
        return array_values($obj['packages']);
    }

    public function serializePaths(array $paths)
    {
        return ['packages' => $paths];
    }
}
