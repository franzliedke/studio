<?php

namespace spec\Studio\Config;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Version2SerializerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Studio\Config\Version2Serializer');
    }

    function it_stores_paths_alphabetically()
    {
        $this->serializePaths(['foo', 'bar'])->shouldReturn(['paths' => ['bar', 'foo']]);
    }

    function it_deduplicates_paths()
    {
        // return array should have no gaps
        $this->serializePaths(['bar', 'foo', 'test', 'foo'])->shouldReturn(['paths' => [0 => 'bar', 1 => 'foo', 2 => 'test']]);
    }
}
