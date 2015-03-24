<?php

namespace AppBundle\Entity;

use AppBundle\Entity\AbstractEntity;
use Doctrine\ORM\Mapping;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends AbstractEntity {
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $_id;
    
    /**
     * @Column(type="string", length="20")
     */
    protected $name;

    /**
     * @Column(type="string", length="60", options={"fixed"=true})
     */
    protected $pass;

    /**
     * @Column(type="string", length="255")
     */
    protected $email;

    /**
     * @Column(type="integer")
     */
    protected $xp;

    /**
     * @Column(type="integer")
     */
    protected $rp;
    
    /**
     * @Column(type="integer")
     */
    protected $dd;

    /**
     * @Column(type="integer")
     */
    protected $prestige;

    /**
     * @Column(type="integer")
     */
    protected $pageviews;

    /**
     * @Column(type="string", length="32", options={"fixed"=true})
     */
    protected $reset_token;
    
}
