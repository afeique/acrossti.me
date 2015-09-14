<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping;

/**
 * @ORM\Entity
 * @ORM\Table(name="stories")
 */
class Story extends AbstractEntity {
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
    protected $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pic_id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="string")
     */
    protected $desc;

    /**
     * @ORM\Column(type="string")
     */
    protected $caption;

}