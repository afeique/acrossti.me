<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping;

/**
 * @ORM\Entity
 * @ORM\Table(name="chapters")
 */
class Chapter extends AbstractEntity {
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
    protected $story_id;

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
    protected $caption;

    /*
    Defaults to LONGTEXT when length option is empty:
    http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html#id38
    */
    /**
     * @ORM\Column(type="text")
     */
    protected $text;
}