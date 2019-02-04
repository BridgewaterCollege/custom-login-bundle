<?php
namespace Tweisman\Bundle\CustomLoginBundle\Security;

use Psr\Container\ContainerInterface;
use SimpleSAML\Auth\Simple;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Tweisman\Bundle\CustomLoginBundle\Security\User\UserCreator;

class LoginSamlAuthenticator extends AbstractGuardAuthenticator
{
    private $requestStack;
    private $router;
    private $em;
    private $csrfTokenManager;
    private $userCreator;
    private $simplesamlAuthObject;

    private $failMessage = 'Invalid credentials';

    public function __construct(RequestStack $requestStack, RouterInterface $router, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, ContainerInterface $container, UserCreator $userCreator, Simple $simplesamlAuthObject) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->em = $entityManager;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->userCreator = $userCreator;
        $this->simplesamlAuthObject = $simplesamlAuthObject;
    }

    public function supports(Request $request)
    {
        if ($request->getPathInfo() == '/login/saml-check') // covers SAML Login and Local
            return true;
        else
            return false;
    }

    public function getCredentials(Request $request)
    {
        $credentials = $this->simplesamlAuthObject->getAuthDataArray();
        if ($credentials == null)
            return false;

        return $credentials;
    }

    public function getUser($simplesamlAuthTokenData, UserProviderInterface $userProvider)
    {
        if (isset($simplesamlAuthTokenData['saml:AuthenticatingAuthority']))
        {
            /**
             * On every login run the user creator, this is going to either create a new record for the logged in user... or update their existing record with whatever was just passed from the
             * assertion. This way they always have their most up-to date information in the local database.
             */
            $this->userCreator->createUser('saml', $simplesamlAuthTokenData);
            $user = $userProvider->loadUserByUsername($simplesamlAuthTokenData['Attributes']['uid'][0]);

            return $user;
        }
        else
        {
            //return error... (this would be the error returned if the token constructor failed)
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($user->getUsername() == $credentials['Attributes']['uid'][0]) {
            return true;
        } throw new CustomUserMessageAuthenticationException($this->failMessage);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        //$requestedUrl = $this->session->get('userRequestedUrl');
        if (isset($requestedUrl)) {
            $url = $requestedUrl;
        } else {
            // Send them to the default target path which in this case is "secure":
            $url = $this->router->generate('custom_login_test_landing');
        }

        return new RedirectResponse($url);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // TODO: Implement onAuthenticationFailure() method.
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        $url = $this->router->generate('custom_login_landing_saml');
        return new RedirectResponse($url);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}