<?php

namespace Studio\Creator;

use Studio\Package;

interface CreatorInterface
{

    /**
     * Create a new package stub.
     *
     * @param Package $package
     * @return void
     */
    public function create(Package $package);

}