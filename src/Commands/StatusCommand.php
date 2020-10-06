<?php
namespace Commands;




use Classes\Helper;
use Classes\Torano\TorConfig;
use Classes\Vpn\VpnConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StatusCommand extends Command
{

    /**
     * @var null|OutputInterface
     */
    private $out = null;



    protected function configure()
    {
        $this->setName('torano:status');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->out = $output;

        Helper::displayToranos($output,Helper::getToranoArray());
        Helper::displayVpn($output,Helper::getToranoArray());
        return 1;
    }

}
