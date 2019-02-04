<?php
namespace Tweisman\Bundle\CustomLoginBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class CreateSimplesamlConfigFiles extends Command
{
    protected $em;
    protected $fileSystem;
    protected $container;

    public function __construct(EntityManagerInterface $em, Filesystem $filesystem, ContainerInterface $container)
    {
        $this->em = $em;
        $this->fileSystem = $filesystem;
        $this->container = $container;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure()
    {
        // to run manually: php bin/console custom-login:create-simplesamlphp-config-files
        $this
            ->setName('custom-login:create-simplesamlphp-config-files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // moves files from the vendor bundle into simplesamlphp's config folders
        $appPath = $this->container->getParameter('kernel.root_dir');

        // Step 1: ensure the config files and simplesamlphp actually exists in this project space, it should because this bundle depends on it right now (1/25/19):
        if ($this->fileSystem->exists($appPath."/../vendor/simplesamlphp/simplesamlphp")) {
            // Warn the user this can overwrite their existing custom config files:
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('This will overwrite any existing custom simplesamlphp config files with the default-templates? Do you wish to proceed (y/n): ', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln("System: Aborting...\r\n");
                return;
            }

            $output->writeln("Copying SimplesamlPHP's config file templates into workspace at ../config/packages/custom-login/simplesamlphp/config\r\n");

            // config files:
            if ($this->fileSystem->exists($appPath."/../config/packages/custom-login/simplesamlphp/config"))
                $this->fileSystem->remove($appPath."/../config/packages/custom-login/simplesamlphp/config");
            $this->fileSystem->mirror($appPath."/../vendor/simplesamlphp/simplesamlphp/config-templates", $appPath."/../config/packages/custom-login/simplesamlphp/config");

            // certs folder:
            $this->fileSystem->mkdir($appPath."/../config/packages/custom-login/simplesamlphp/cert");

            // metadata folder:
            if ($this->fileSystem->exists($appPath."/../config/packages/custom-login/simplesamlphp/metadata"))
                $this->fileSystem->remove($appPath."/../config/packages/custom-login/simplesamlphp/metadata");
            $this->fileSystem->mirror($appPath."/../vendor/simplesamlphp/simplesamlphp/metadata-templates",$appPath."/../config/packages/custom-login/simplesamlphp/metadata");

            $this->fileSystem->chown($appPath."/../config/packages/custom-login", "apache", true);
            $this->fileSystem->chmod($appPath."/../config/packages/custom-login", 0755, 0000, true);

            $output->writeln("Files Successfully Created!\r\n");
        } else {
            $output->writeln("Warning: SimplesamlPHP doesn\'t appear in the current vendor workspace, make sure you\'ve installed simplesamlphp with composer.\r\n");
        }
    }
}