<?php
namespace BridgewaterCollege\Security\Authentication\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SimplesamlPHPAuthenticationProvider implements AuthenticationProviderInterface
{
    public function __construct(UserProviderInterface $userProvider, CacheItemPoolInterface $cachePool)
    {
        // Lands here first on successful firewall import.
        $this->userProvider = $userProvider;
        $this->cachePool = $cachePool;
    }

    public function authenticate(TokenInterface $token)
    {
        return $token;
    }

    public function supports(TokenInterface $token)
    {
        // TODO: Implement supports() method.
    }
}