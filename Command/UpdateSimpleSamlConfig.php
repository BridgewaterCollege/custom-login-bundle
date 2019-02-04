<?php
namespace Tweisman\Bundle\CustomLoginBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class UpdateSimpleSamlConfig extends Command
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
        // to run manually: php bin/console custom-login:update-simplesamlphp-config
        $this
            ->setName('custom-login:update-simplesamlphp-config');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // moves files from the vendor bundle into simplesamlphp's config folders
        $appPath = $this->container->getParameter('kernel.root_dir');
        // Step 1: ensure the config files and simplesamlphp actually exists in this project space, it should because this bundle depends on it right now (1/25/19):
        if ($this->fileSystem->exists($appPath."/../vendor/simplesamlphp/simplesamlphp")) {
            if ($this->fileSystem->exists($appPath."/../config/packages/custom-login/simplesamlphp/config")) {
                // Then copy the "config, certs, and metadata files" in your working ../config dir to simplesamlphp's config dir
                $this->fileSystem->mirror($appPath."/../config/packages/custom-login/simplesamlphp", $appPath."/../vendor/simplesamlphp/simplesamlphp");
            } else {
                $output->writeln("Warning: you've not created custom simplesamlphp config templates in the local workspace. Please run custom-login:create-simplesamlphp-config-files and customize 
                those files accordingly.\r\n");
            }
        } else {
            $output->writeln("Warning: SimplesamlPHP doesn\'t appear in the current vendor workspace, make sure you\'ve installed simplesamlphp with composer.\r\n");
        }
    }
}