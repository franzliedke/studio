<?php

namespace Studio\Parts;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleInput implements PartInputInterface
{

    /**
     * @var DialogHelper
     */
    protected $dialog;

    /**
     * @var OutputInterface
     */
    protected $output;


    public function __construct(DialogHelper $dialog, OutputInterface $output)
    {
        $this->dialog = $dialog;
        $this->output = $output;
    }

    public function ask($question)
    {
        return $this->dialog->ask($this->output, $question);
    }

}
