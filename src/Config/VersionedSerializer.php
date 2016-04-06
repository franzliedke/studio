<?php

namespace Studio\Config;

class VersionedSerializer implements Serializer
{
    /**
     * @var Serializer[]
     */
    protected $serializers;

    /**
     * @var int
     */
    protected $defaultVersion;

    /**
     * @param int $version
     * @param Serializer $serializer
     * @return static
     */
    public static function withDefault($version, Serializer $serializer)
    {
        return new static([$version => $serializer], $version);
    }

    public function __construct(array $serializers, $defaultVersion)
    {
        $this->serializers = $serializers;
        $this->defaultVersion = $defaultVersion;
    }

    public function version($version, Serializer $serializer)
    {
        $this->serializers[$version] = $serializer;

        return $this;
    }

    public function deserializePaths($obj)
    {
        if (!isset($obj['version'])) {
            $serializer = $this->serializers[$this->defaultVersion];
        } else if (array_key_exists(intval($obj['version']), $this->serializers)) {
            $serializer = $this->serializers[$obj['version']];
        } else {
            throw new \InvalidArgumentException('Invalid version');
        }

        return $serializer->deserializePaths($obj);
    }

    public function serializePaths($paths)
    {
        $lastVersion = max(array_keys($this->serializers));
        $serializer = $this->serializers[$lastVersion];

        return ['version' => $lastVersion] + $serializer->serializePaths($paths);
    }
}
