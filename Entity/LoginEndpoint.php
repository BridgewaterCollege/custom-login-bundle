<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="login_endpoints")
 */
class LoginEndpoint
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public $endpointId;

    /**
     * @ORM\Column(type="text")
     */
    public $endpointName;

    /**
     * @ORM\Column(type="text")
     */
    public $endpointLocalUrl;

    /**
     * @ORM\Column(type="integer")
     */
    public $endpointDefault;

    /**
     * @ORM\Column(type="integer")
     */
    public $endpointEnabled;
}