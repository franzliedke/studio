<?php

namespace Studio\Parts;

interface PartInputInterface
{

    public function ask($question, callable $validator, $default = null);

}
