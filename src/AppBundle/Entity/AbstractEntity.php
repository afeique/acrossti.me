<?php

namespace AppBundle\Entity;

/**
 * http://www.epixa.com/2010/05/the-best-models-are-easy-models.html
 */

class AbstractEntity {
    /**
     * Map a call to get a property to its corresponding accessor if it exists.
     * Otherwise, get the property directly.
     *
     * Check for read-only attributes which have property names preceded by an
     * underscore.
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

        throw new \LogicException(sprintf(
            "No property named '%s' exists",
            $name
        ));
    }

    /**
     * Map a call to set a property to its corresponding mutator if it exists.
     * Otherwise, set the property directly.
     *
     * Throw an exception for properties that begin with an underscore allowing
     * for read-only attributes.
     * 
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws \LogicException If no mutator/property exists by that name
     * @throws \LogicException If trying to set a read-only property
     */
    public function __set($name, $value) {
        $fn = "set". ucfirst($name);
        if (method_exists($this, $fn)) {
            return $this->$fn($value);
        } 

        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        } else if (property_exists($this, "_$name")) {
            throw new \LogicException(sprintf(
                "Trying to set read-only property '%s'",
                $name
            ));
        }

        throw new \LogicException(sprintf(
            "No property named '%s' exists",
            $name
        ));
    }
}
