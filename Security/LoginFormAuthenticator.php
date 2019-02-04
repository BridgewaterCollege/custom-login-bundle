<?php
namespace Tweisman\Bundle\CustomLoginBundle\Security;

use SimpleSAML\Auth\Simple;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Tweisman\Bundle\CustomLoginBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $simplesamlAuthObject;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        //return 'custom_login_landing_local' === $request->attributes->get('_route')
        //&& $request->isMethod('POST');
        if (($request->attributes->get('_route') === 'custom_login_landing_local' && $request->isMethod('POST')))
            return true;
        else
            return false;
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // Local Login
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // This checks if the user "currently" logged in is still valid
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            // redirect to where ever they were trying to go before being prompted for login
            $requestedPath = $request->getSession()->get('userRequestedUrl');
            if (isset($requestedPath) && $requestedPath != '')
                $url = $requestedPath;
            else
                $url = $request->getBaseUrl()."/secure/test-secure-landing";
            return new RedirectResponse($url);
        } else {
            // default login if they went straight to the login form itself...
            $url = $request->getBaseUrl()."/secure/test-secure-landing";
            return new RedirectResponse($url);
        }

        // For example : return new RedirectResponse($this->router->generate('some_route'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo: add some code to redirect back and display errors on failure, maybe explain password and/or username are incorrect
    }

    public function supportsRememberMe()
    {
        return false;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('custom_login_landing');
    }
}