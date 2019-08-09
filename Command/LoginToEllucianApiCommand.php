<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

// Additional Includes:
use BridgewaterCollege\Bundle\CustomLoginBundle\Utils\EllucianColleagueApiHandler;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginToEllucianApiCommand extends Command
{
    protected $ellucianColleagueApiHandler;
    protected $session;

    public function __construct(EllucianColleagueApiHandler $ellucianColleagueApiHandler, SessionInterface $session)
    {
        $this->ellucianColleagueApiHandler = $ellucianColleagueApiHandler;
        $this->session = $session;
        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure()
    {
        // to run manually: php bin/console custom-login:login-to-ellucian-colleague-api
        $this
            ->setName('custom-login:login-to-ellucian-colleague-api');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->ellucianColleagueApiHandler->loginToEllucianColleagueApi()) {
            $output->writeln('Colleague API Authenticated Successfully');
        }
    }
}