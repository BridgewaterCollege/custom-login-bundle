<?php
// src/Controller/SecurityController.php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use SimpleSAML\Auth\Simple;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Custom Includes:
use BridgewaterCollege\Bundle\CustomLoginBundle\Utils\LoginHandler;

class SecurityController extends AbstractController
{
    private $localRedirectUrl;

    public function login(Request $request, SessionInterface $session){
        $lastUrl = isset($session->get('last_route')['name']) ? $session->get('last_route')['name']: 'internal-main-landing';
        $session->set('original_user_requested_route', $lastUrl);

        /** Default Login Url: grabs the "default" login path set in the applications database and kicks off the process */
        $LoginHandler = $this->container->get('bridgewater_college_custom_login.process_handler.login_handler');
        $defaultLoginPath = $LoginHandler->getDefaultLoginPath();
        $redirectUrl = "".$request->getBaseUrl()."/".$defaultLoginPath;

        return $this->redirect($redirectUrl);
    }

    public function loginLocal(AuthenticationUtils $authenticationUtils) {
        $LoginHandler = $this->container->get('bridgewater_college_custom_login.process_handler.login_handler');
        if (!$LoginHandler->getLoginPathEnabled(1))
            return $this->redirectToRoute('custom_login_landing');

        // System: ensure they aren't already authenticated this session

        // Local Login Point (LocalFormAuthenticator takes over here)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $twigFile = '@bridgewater_college_custom_login/login-local.html.twig';
        return $this->render($twigFile, [
            'last_username' => null,
            'error' => $error,
        ]);
    }

    public function loginSaml(Request $request, Simple $as) {
        $LoginHandler = $this->container->get('bridgewater_college_custom_login.process_handler.login_handler');
        if (!$LoginHandler->getLoginPathEnabled(2))
            return $this->redirectToRoute('custom_login_landing');

        // System: ensure they aren't already authenticated this session
        $auth_checker = $this->get('security.authorization_checker');
        if ($auth_checker->isGranted('ROLE_USER'))
            return $this->redirectToRoute('custom_login_test_landing');

        $as->requireAuth();
        return $this->redirectToRoute('custom_login_landing_saml_check');
    }

    public function loginSamlCheck(Request $request, Simple $as) {
        $LoginHandler = $this->container->get('bridgewater_college_custom_login.process_handler.login_handler');
        if (!$LoginHandler->getLoginPathEnabled(2))
            return $this->redirectToRoute('custom_login_landing');

        /** SAML Authenticator takes over here */
        //$as = $this->get('bridgewater_college_custom_login.simplesamlphp_auth_object');
        if (!$as->isAuthenticated()) {
            return $this->redirectToRoute('custom_login_test_landing');
        }
    }

    public function loginTestLanding(Request $request) {
        if (isset($this->localRedirectUrl))
            return $this->redirect($this->localRedirectUrl);

        $twigFile = '@bridgewater_college_custom_login/test-secure-landing.html.twig';
        return $this->render($twigFile, [
        ]);
    }

    public function setConfig($localRedirectUrl) {
        $this->localRedirectUrl = $localRedirectUrl;
    }
}