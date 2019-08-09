<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SimplesamlPHPAuthToken extends AbstractToken
{
    public $sessionIndex;
    public $attributes;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has "local symfony" roles, consider it authenticated, these can but don't have to be your actual permissions. For example I've used group membership in Active Directory in place of these
        // in the past. Symfony/SAML just needs a "way" to know the token is good/valid
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }
}