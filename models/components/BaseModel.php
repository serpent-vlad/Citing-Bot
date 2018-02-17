<?php

namespace models\components;

use ReflectionClass;
use yii\base\BaseObject;

/**
 * Class BaseModel
 * @package models\components
 * @property array $attributes  Attribute values (name => value)
 */
class BaseModel extends BaseObject
{
    /**
     * @param null  $names
     * @param array $except
     * @return array
     * @throws \ReflectionException
     */
    public function getAttributes($names = null, $except = [])
    {
        $values = [];
        if ($names === null) {
            $names = $this->attributes();
        }
        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }
        foreach ($except as $name) {
            unset($values[$name]);
        }
        return $values;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function attributes()
    {
        $class = new ReflectionClass($this);
        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }
        return $names;
    }
}