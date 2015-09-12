<?php

namespace AppBundle\Entity;

/**
 * http://www.epixa.com/2010/05/the-best-models-are-easy-models.html
 */
/**
 * Actual database columns are named using snake-case convention due to
 * ambiguities in case-sensitivity between different SQL backends, but model
 * attributes are all named according to camelCase convention.
 *
 * Read-only properties are attributes whose names are preceded by an
 * underscore. These properties can be accessed, but attempting to setm them
 * will throw a LogicException.
 */
class AbstractEntity {
    /**
     * Map a call to get a property to its corresponding accessor if it exists.
     * Otherwise, get the property directly.
     *
     * @param  string $name
     * @return mixed
     * @throws \LogicException If no accessor/property exists by that name
     */
    public function __get($name) {
        $fn = "get". ucfirst($name);
        if (method_exists($this, $fn)) {
            return $this->$fn();
        } 

        $_name = "_$name";
        if (property_exists($this, $name)) {
            return $this->$name;
        } else if (property_exists($this, $_name)) {
            return $this->$_name;
        }

        throw new \LogicException(
            "No property named '$name' exists"
        );
    }

    /**
     * Map a call to set a property to its corresponding mutator if it exists.
     * Otherwise, set the property directly.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws \LogicException If no mutator/property exists by that name
     * @throws \LogicException If trying to set read-only property
     */
    public function __set($name, $value) {
        $fn = "set". ucfirst($name);
        if (method_exists($this, $fn)) {
            return $this->$fn($value);
        } 

        $_name = "_$name";
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        } else if (property_exists($this, $_name)) {
            throw new \LogicException(
                "Trying to set read-only property '$name'"
            );
        }

        throw new \LogicException(
            "No property named '$name' exists"
        ));
    }
}
