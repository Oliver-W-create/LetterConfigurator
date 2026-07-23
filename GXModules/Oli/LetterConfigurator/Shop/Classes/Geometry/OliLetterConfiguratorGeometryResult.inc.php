<?php

/**
 * Immutable value object returned by the server-side geometry service.
 */
class OliLetterConfiguratorGeometryResult implements JsonSerializable
{
    /** @var array<string, mixed> */
    private $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
