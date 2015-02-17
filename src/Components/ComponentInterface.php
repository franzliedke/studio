<?php

namespace Studio\Components;

interface ComponentInterface
{

    public function getName();

    public function configureComposer($config);

}
