<?php

namespace tes\CmsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Humbug\SelfUpdate\Updater;
use tes\CmsBuilder\RemoteSha1Strategy;

class SelfUpdateCommand extends Command
{

    protected $cwd;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates the cms-builder command');
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $strategy = new RemoteSha1Strategy();
        $updater = new Updater(null, false);
        $updater->setStrategyObject($strategy);
        $updater->getStrategy()->setPharUrl('https://github.com/tes/cms-builder/raw/master/cms-builder.phar');
        $result = $updater->update();
        $output->writeln($result ? 'Updated!' : 'No update needed!');
    }

}
