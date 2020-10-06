<?php
namespace Commands;



use Classes\Helper;

use Classes\Torano\TorConfig;
use Classes\Torano\TorElement;
use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ResetCommand extends Command
{


    protected function configure()
    {
        $this->setName('torano:reset');
    }





    protected function execute(InputInterface $input, OutputInterface $output)
    {



        $baseCache = Helper::getCache();

        $proxyArray = $baseCache->get(TorConfig::SAVE_ARRAY);
        /* @var $proxyArray TorElement[] */

        foreach($proxyArray as $index=>$proxy){
            $proxyArray[$index]->setNumCalled(0);
            $proxyArray[$index]->setNumCallError(0);
            $proxyArray[$index]->setErrorMsg('Reset '.date("H:i"));
        }
        $baseCache->set(TorConfig::SAVE_ARRAY,$proxyArray,TorConfig::SAVE_TIME);

        echo "Reset!";
        echo "\n";

        return true;
    }







}
