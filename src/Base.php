<?php

namespace JSON2Video;

class Base {

    protected $object, $properties;

    public function __get($property) {
        $property = strtolower($property);
        if (in_array($property, $this->properties) && isset($this->object[$property])) {
            return $this->object[$property];
        }

        return null;
    }

    public function __set($property, $value) {
        $property = strtolower($property);
        if (in_array($property, $this->properties)) {
            $property = strtolower(str_replace('_', '-', $property));
            $this->object[$property] = $value;
            return $value;
        }

        return null;
    }

    public function addElement($element=null) {
        if ($element && is_array($element)) {
            if (!isset($this->object['elements'])) $this->object['elements'] = [];
            $this->object['elements'][] = $element;
            return true;
        }
        return false;
    }

    public function getJSON() {
        return json_encode($this->object, JSON_PRETTY_PRINT);
    }

    public function getObject() {
        return $this->object;
    }

    // Added in v2
    public function setJSON($object) {
        if (is_array($object)) $this->object = $object;
        elseif (is_string($object)) $this->object = json_decode($object, true);
    }
}