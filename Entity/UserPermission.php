<?php
namespace Tweisman\Bundle\CustomLoginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_permissions")
 */
class UserPermission
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $permId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="permissions", cascade={"persist", "merge"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $user;

    /**
     * @ORM\Column(type="text")
     */
    protected $permName;

    public function getPermId()
    {
        return $this->permId;
    }
    public function setPermId($permId)
    {
        $this->permId = $permId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUser()
    {
        return $this->user;
    }
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getPermName()
    {
        return $this->permName;
    }
    public function setPermName($permName)
    {
        $this->permName = $permName;
    }
}