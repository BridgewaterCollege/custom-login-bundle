<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Utils;

use BridgewaterCollege\Bundle\CustomLoginBundle\Entity\EllucianColleagueApi;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EllucianColleagueApiHandler extends ProcessHandler {

    public function createEllucianApiAccount($username, $userPass, $accountUrl) {
        if (!$ellucianColleagueApi = $this->em->getRepository(EllucianColleagueApi::class)->find(1))
            $ellucianColleagueApi = new EllucianColleagueApi();
        $ellucianColleagueApi->username = $username;

        $ellucianColleagueApi->setIv(base64_encode(openssl_random_pseudo_bytes(16, $strong)));
        $ellucianColleagueApi->setPassword($this->encryptor->encrypt($userPass, base64_decode($ellucianColleagueApi->getIv()), 16));
        $ellucianColleagueApi->colleagueWebApiUrl = $accountUrl;

        $this->em->persist($ellucianColleagueApi);
        $this->em->flush();
    }

    private function getEllucianPass($encryptedPass, $iv) {
        return $this->encryptor->decrypt($encryptedPass, base64_decode($iv));
    }

    public function loginToEllucianColleagueApi() {
        $ellucianConfig = $this->getEllucianColleagueApiConfig();
        $data = array('UserId' => $ellucianConfig->username, 'Password' => $this->getEllucianPass($ellucianConfig->getPassword(), $ellucianConfig->getIv()));
        $body = \Unirest\Request\Body::form($data);

        $headers = array('Accept' => 'application/vnd.ellucian.v1+json', 'Cache-Control' => 'no-cache');
        $response = \Unirest\Request::post('https://arrow.bridgewater.edu:8320/colleagueapi/session/login', $headers, $body);

        if (isset($response->headers[0]) && $response->headers[0] == 'HTTP/1.1 401 Unauthorized') {
            throw new HttpException(500, $response->body);
        }

        $this->session->set('ColleagueApiSessionToken', $response->body);
        return true;
    }

    public function logoutEllucianColleagueApi() {
        $ellucianConfig = $this->getEllucianColleagueApiConfig();
        $headers = array('Accept' => 'application/vnd.ellucian.v1+json', 'Cache-Control' => 'no-cache', 'X-CustomCredentials' => $this->session->get('ColleagueApiSessionToken'));
        \Unirest\Request::post('https://arrow.bridgewater.edu:8320/colleagueapi/session/logout', $headers, null);

        $this->session->remove('ColleagueApiSessionToken');
    }

    public function proxyLoginToEllucianColleagueApi() {
        if ($this->security->getUser() == null)
            throw new HttpException(500, "Error, proxy authentication not allowed.");

        $ellucianConfig = $this->getEllucianColleagueApiConfig();
        $data = array('ProxyId' => $ellucianConfig->username, 'ProxyPassword' => $this->getEllucianPass($ellucianConfig->getPassword(), $ellucianConfig->getIv()), 'UserId' => $this->security->getUser()->getUsernameNoDomain());
        $body = \Unirest\Request\Body::form($data);

        $headers = array('Accept' => 'application/vnd.ellucian.v1+json', 'Cache-Control' => 'no-cache');
        $response = \Unirest\Request::post('https://arrow.bridgewater.edu:8320/colleagueapi/session/proxy-login', $headers, $body);

        if (isset($response->headers[0]) && $response->headers[0] == 'HTTP/1.1 401 Unauthorized') {
            throw new HttpException(500, $response->body);
        }

        $this->session->set('ColleagueApiSessionToken', $response->body);
    }

    private function getEllucianColleagueApiConfig() {
        if (!$ellucianColleagueApi = $this->em->getRepository(EllucianColleagueApi::class)->find(1))
            throw new HttpException(500, "Error no ellucain api configured in system, please run custom-login:create-ellucian-colleague-api to continue...");

        return $ellucianColleagueApi;
    }
}