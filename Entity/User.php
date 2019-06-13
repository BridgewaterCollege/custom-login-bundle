<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use BridgewaterCollege\Bundle\CustomLoginBundle\Entity\UserPermission;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $user_id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $usertype;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fullname;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $roles = array();

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $loginType;

    /**
     * @ORM\OneToMany(targetEntity="UserPermission", mappedBy="user", cascade={"persist", "merge"})
     */
    private $permissions;


    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param string $user_id
     *
     * @return User
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsertype()
    {
        return $this->usertype;
    }

    /**
     * @param string $userType
     *
     * @return User
     */
    public function setUsertype($usertype)
    {
        $this->usertype = $usertype;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @param string $fullname
     *
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getUsernameNoDomain() {
        $username = $this->username;
        if (strpos($this->username, '@') !== false) {
            $username = explode('@', $username);
            $username = $username[0];
        }

        return $username;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setLoginType(string $loginType): self
    {
        $this->loginType = $loginType;
        return $this;
    }
    
    public function getLoginType()
    {
        return $this->loginType;
    }

    public function getPermissions() {
        return $this->permissions;
    }
    public function setPermissions($permissions) {
        $this->permissions = $permissions;
    }
    public function hasPermissions($permissionsRequired) {
        /**
         * Allows you to run @Security("user.hasPermissions(['Perm_Name_1', 'Perm_Name_2'])") from symfony's controller annotation to permit/deny access to a page or section of the application
         */
        if (is_iterable($this->permissions) && is_array($permissionsRequired)) {
            foreach ($this->permissions as $i=>$permObject) {
                if (in_array($permObject->getPermName(), $permissionsRequired))
                    return true;
            }
        }

        return false;
    }

    public function addUserPermission($permission) {
        $this->permissions->add($permission);
        $permission->setUser($this);
    }
    public function removeUserPermission($permission) {
        $this->permissions->removeElement($permission);
        //$permission->setUser(null);
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        return '';
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([$this->user_id, $this->username, $this->roles, $this->password]);
    }

    /**
     * @return void
     */
    public function unserialize($serialized)
    {
        list($this->user_id, $this->username, $this->roles, $this->password) = unserialize($serialized);
    }
}
