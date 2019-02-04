<?php
namespace Tweisman\Bundle\CustomLoginBundle\ProcessHandlers;

use Tweisman\Bundle\CustomLoginBundle\Entity\LoginEndpoint;

/**
 * Class Type: Process Handler
 */
class LoginHandler extends ProcessHandler
{
    public function test() {
        echo "in here testing!!";
    }

    public function getDefaultLoginPath() {
        $defaultEndpoint = $this->em->getRepository(LoginEndpoint::class)->findBy(array('endpointDefault'=>1));
        if (isset($defaultEndpoint[0])) {
            return $defaultEndpoint[0]->endpointLocalUrl;
        }
    }

    public function getLoginPathEnabled($endpointId) {
        $endpoint = $this->em->getRepository(LoginEndpoint::class)->find($endpointId);
        return $endpoint->endpointEnabled;
    }
}