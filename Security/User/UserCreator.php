<?php
// ..src/BCCustomSSOBundle/Security/User/UserCreator.php
namespace Tweisman\Bundle\CustomLoginBundle\Security\User;

use Doctrine\ORM\EntityManagerInterface;
use Tweisman\Bundle\CustomLoginBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tweisman\Bundle\CustomLoginBundle\Entity\UserPermission;

class UserCreator implements UserCreatorInterface
{
    private $em;
    private $primaryKey;
    private $requiredAttributes;
    private $samlAttributeMappings;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function setConfig($config) {
        if (isset($config['required_attributes']) && !is_array($config['required_attributes']))
            throw new HttpException(500, "Error invalid yaml: please pass required_attributes as an array []");

        if (isset($config['required_attributes']))
            $this->requiredAttributes = $config['required_attributes'];

        $this->primaryKey = $config['primary_key'];
        $this->samlAttributeMappings = $config['user_mappings'];
    }

    public function createUser($userType, $data)
    {
        if ($userType == 'saml') {
            $this->createSamlUser($data);
        } else {
            // todo: add user creation for local user
        }
    }

    private function createSamlUser($simplesamlAuthTokenData)
    {
        /**
         * Generic user creation code/mapping example on how to access passed attributes out of the assertion
         */
        //print_r($simplesamlAuthTokenData['Attributes']);
        if (isset($simplesamlAuthTokenData['Attributes'])) {
            // check to make sure the required attributes exist in the system:
            foreach ($this->requiredAttributes as $key) {
                if (!array_key_exists($key, $simplesamlAuthTokenData['Attributes']))
                    throw new HttpException(500, "The " . $key . " attribute is required and was not found in the assertion. Make sure you're passing the required attributes in the assertion.");
            }

            // if all required attributes were passed correctly proceed to mapping:
            $newUser = $this->em->getRepository(User::class)->find($simplesamlAuthTokenData['Attributes'][$this->primaryKey][0]);
            $newUser = ($newUser == null ? new User() : $newUser);

            foreach ($this->samlAttributeMappings as $mappings) {
                foreach ($mappings as $samlKey => $userField) {
                    $setter = 'set' . ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $userField))));
                    if (method_exists($newUser, $setter))
                        $newUser->$setter($simplesamlAuthTokenData['Attributes'][$samlKey][0]);

                    // User Permissions:
                    if ($userField == 'user_permissions' && is_array($simplesamlAuthTokenData['Attributes'][$samlKey])) {
                        // Step 1: clean-up old permissions
                        foreach ($newUser->getPermissions() as $i=>$oldPermission) {
                            if (!in_array($oldPermission->getPermName(), $simplesamlAuthTokenData['Attributes'][$samlKey])) {
                                $newUser->removeUserPermission($oldPermission);
                                $this->em->remove($oldPermission);
                            }
                        }

                        // Step 2: add new permissions
                        foreach ($simplesamlAuthTokenData['Attributes'][$samlKey] as $i=>$permissionName) {
                            $permission = new UserPermission();

                            // check if the new permission is already in the system, if so skip re-adding it
                            foreach ($newUser->getPermissions() as $a=>$oldPermission) {
                                if ($oldPermission->getPermName() == $permissionName) {
                                    $permission = $oldPermission;
                                }
                            }

                            $permission->setPermName($permissionName);
                            $newUser->addUserPermission($permission);
                        }
                    }
                }
            }

            $newUser->setRoles(["ROLE_USER"]);
            $newUser->setLoginType('saml');

            $this->em->merge($newUser);
            $this->em->flush();
        }

    }
}