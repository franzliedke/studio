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

    public function confirm($question, $default = false)
    {
        $options = $default ? 'Y|n' : 'y|N';

        return $this->dialog->askConfirmation(
            $this->output,
            "<question>$question [$options]</question> ",
            $default
        );
    }

    public function ask($question, $regex, $default = null)
    {
        if ($default) $question = "$question [$default]";

        return $this->dialog->askAndValidate(
            $this->output,
            "<question>$question</question> ",
            $this->validateWith($regex),
            false,
            $default
        );
    }

    protected function validateWith($regex)
    {
        return function ($answer) use ($regex) {
            if (preg_match($regex, $answer)) return $answer;

            throw new \RuntimeException('Invalid. Try again.');
        };
    }

}
