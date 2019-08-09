<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ellucian_colleague_api")
 */
class EllucianColleagueApi
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=191)
     */
    public $username;

    /**
     * @ORM\Column(type="string", length=191)
     */
    protected $password;

    /**
     * @ORM\Column(type="text")
     */
    protected $iv;

    /**
     * @ORM\Column(type="text")
     */
    public $colleagueWebApiUrl;

    public function setPassword($encryptedPass) {
        $this->password = $encryptedPass;
    }
    public function getPassword()
    {
        return $this->password;
    }

    public function setIv($iv) {
        $this->iv = $iv;
    }
    public function getIv() {
        return $this->iv;
    }
}