<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="genres")
 */
class Genre extends AbstractEntity {
    /*
    ORM\GeneratedValue strategy types:
    http://doctrine-orm.readthedocs.org/en/latest/reference/basic-mapping.html#identifier-generation-strategies
    */
    /**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $parent_genre_id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $desc;

}
