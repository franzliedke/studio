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
        return $this->dialog->askConfirmation(
            $this->output,
            "<question>$question</question> ",
            $default
        );
    }

    public function ask($question, callable $validator, $default = null)
    {
        return $this->dialog->askAndValidate(
            $this->output,
            "<question>$question</question> ",
            $this->validateWith($validator),
            false,
            $default
        );
    }

    protected function validateWith($validator)
    {
        return function ($answer) use ($validator) {
            if ($validator($answer)) return $answer;

            throw new \RuntimeException('Invalid. Try again.');
        };
    }

}
