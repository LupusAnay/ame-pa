<?php


abstract class AbstractModel implements JsonSerializable
{
    const ID = 'id';
    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function jsonSerialize()
    {
        return $this->__dict();
    }

    public function __dict()
    {
        $result = [];
        $reflection = new ReflectionObject($this);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            $value = $property->getValue($this);
            $result[$name] = $value;
            $property->setAccessible(false);
        }
        return $result;
    }

    public function update(array $data)
    {
        $reflection = new ReflectionObject($this);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            if ($property->getName() == self::ID) {
                continue;
            }

            $property->setAccessible(true);
            if (array_key_exists($property->getName(), $data)) {
                $property->setValue($this, $data[$property->getName()]);
            }
            $property->setAccessible(false);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }
}