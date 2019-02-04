<?php
// src/Controller/SecurityController.php
namespace Tweisman\Bundle\CustomLoginBundle\Controller;

use SimpleSAML\Auth\Simple;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Custom Includes:
use Tweisman\Bundle\CustomLoginBundle\ProcessHandlers\LoginHandler;

class SecurityController extends AbstractController
{
    public function login(Request $request, SessionInterface $session){
        /** Default Login Url: grabs the "default" login path set in the applications database and kicks off the process */
        $LoginHandler = $this->container->get('tweisman_custom_login.process_handler.login_handler');
        $defaultLoginPath = $LoginHandler->getDefaultLoginPath();
        $redirectUrl = "".$request->getBaseUrl()."/".$defaultLoginPath;

        return $this->redirect($redirectUrl);
    }

    public function loginLocal(AuthenticationUtils $authenticationUtils) {
        $LoginHandler = $this->container->get('tweisman_custom_login.process_handler.login_handler');
        if (!$LoginHandler->getLoginPathEnabled(1))
            return $this->redirectToRoute('custom_login_landing');

        // System: ensure they aren't already authenticated this session

        // Local Login Point (LocalFormAuthenticator takes over here)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $twigFile = '@tweisman_custom_login/login-local.html.twig';
        return $this->render($twigFile, [
            'last_username' => null,
            'error' => $error,
        ]);
    }

    public function loginSaml(Request $request, Simple $as) {
        $LoginHandler = $this->container->get('tweisman_custom_login.process_handler.login_handler');
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
        $LoginHandler = $this->container->get('tweisman_custom_login.process_handler.login_handler');
        if (!$LoginHandler->getLoginPathEnabled(2))
            return $this->redirectToRoute('custom_login_landing');

        /** SAML Authenticator takes over here */
        //$as = $this->get('tweisman_custom_login.simplesamlphp_auth_object');
        if (!$as->isAuthenticated()) {
            return $this->redirectToRoute('custom_login_test_landing');
        }
    }

    public function loginTestLanding(Request $request) {
        $twigFile = '@tweisman_custom_login/test-secure-landing.html.twig';
        return $this->render($twigFile, [
        ]);
    }
}