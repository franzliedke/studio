<?php

namespace Studio\Parts;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleInput implements PartInputInterface
{

    protected $input;

    protected $output;


    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function ask($question)
    {
        // TODO: Implement ask() method.
    }

}
