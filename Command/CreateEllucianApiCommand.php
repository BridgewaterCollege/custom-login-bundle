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

class CreateEllucianApiCommand extends Command
{
    protected $ellucianColleagueApiHandler;

    public function __construct(EllucianColleagueApiHandler $ellucianColleagueApiHandler)
    {
        $this->ellucianColleagueApiHandler = $ellucianColleagueApiHandler;
        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure()
    {
        // to run manually: php bin/console custom-login:create-ellucian-colleague-api
        $this
            ->setName('custom-login:create-ellucian-colleague-api');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will overwrite the existing colleague api connection, do you wish to proceed (y/n): ', false);
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("System: Aborting...\r\n");
            return;
        }

        $question = new Question('Please enter the colleague proxy account\'s username: ');
        while(!$username = $helper->ask($input, $output, $question)) {
            $output->writeln('Warning! username cannot be empty');
        }

        $question = new Question('Please enter the colleague proxy account\'s password: ');
        $question->setHidden(true);
        while(!$password = $helper->ask($input, $output, $question)) {
            $output->writeln('Warning! password cannot be empty');
        }

        $question = new Question('Please enter your colleague api\'s base url: ');
        while(!$url = $helper->ask($input, $output, $question)) {
            $output->writeln('Warning! url cannot be empty');
        }

        // once all three prompts are completed, proceed to inserting the new information into the database:
        $this->ellucianColleagueApiHandler->createEllucianApiAccount($username, $password, $url);
    }
}