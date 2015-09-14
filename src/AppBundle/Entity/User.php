<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends AbstractEntity {
    /*
    ORM\GeneratedValue strategy types, see:
    http://doctrine-orm.readthedocs.org/en/latest/reference/basic-mapping.html#identifier-generation-strategies
    */
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $_id;
    
    /**
     * @ORM\Column(length="20")
     */
    protected $name;

    /**
     * @ORM\Column(length="60", options={"fixed"=true})
     */
    protected $pass;

    /**
     * @ORM\Column
     */
    protected $email;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pageviews;

    /**
     * @ORM\Column(type="integer")
     */
    protected $votes;

    /**
     * @ORM\Column(type="integer")
     */
    protected $xp;

    /**
     * @ORM\Column(type="integer")
     */
    protected $rp;

    /**
     * @ORM\Column(type="integer")
     */
    protected $dd;

    /**
     * @ORM\Column(name="reset_token", length="32", options={"fixed"=true})
     */
    protected $_resetToken;

    protected function randStr($len=10) 
    {
        $str = '';
        for ($i=0; $i<$len; $i++) {
            $nm = range('0','9');
            $lc = range('a','z');
            $uc = range('A','Z');
            $a = array_merge($nm, $lc, $uc);
            
            $str .= $a[mt_rand(0, sizeof($a)-1)];
        }

        return $str;
    }

    public function setPass($p) 
    {
        $opt = ["cost" => 10];
        $this->pass = password_hash($p, PASSWORD_BCRYPT, $opt);
    }

    public function verifyPass($p) 
    {
        return password_verify($p, $this->pass);
    }

    public function genResetToken() 
    {
        $this->_resetToken = md5($this->randStr());
    }
}
