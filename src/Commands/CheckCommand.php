<?php

namespace Commands;




use Classes\Helper;
use Classes\Torano\TorConfig;
use Classes\Torano\TorElement;

use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;


class CheckCommand extends Command
{

    /**
     * @var null|OutputInterface
     */
    private $out = null;

    /**
     * @var TorElement[]
     */
    private $failureArray = [];

    /**
     * @var TorElement[]
     */
    private $proxyArray = [];


    protected function configure()
    {
        $this->setName('torano:check');
    }


    /**
     * @param $elements TorElement[]
     */
    private function updateInformations(&$elements)
    {

        foreach ($elements as &$torElement) {
            $this->out->writeln("<info>Update Latency for ".$torElement->getPort()."</info>");
            Helper::updateLatencyAndIp($torElement);
            $this->out->writeln("<info>Latency for ".$torElement->getLatency()."</info>");
        }
    }

    /**
     * @param $elements TorElement[]
     */
    private function checkInformations(&$elements)
    {
        $ips = [];
        foreach ($elements as $index => &$element) {
            /*@var $element TorElement */
            $element->lastChecked = time();
            if (in_array($element->getIp(), $ips)) {
                $element->setStatus(TorElement::STATUS_ERR);
                $element->setLastError("Duplicate IP");
                array_push($this->failureArray, $element);
                $this->restartPort($element->getPort());
                //unset($elements[$index]);
                continue;
            } else {
                $ips[] = $element->getIp();
            }
            if ((int)$element->getLatency() === 0 || $element->getLatency() > TorConfig::MIN_LATENCY) {
                $element->setStatus(TorElement::STATUS_ERR);
                $element->setLastError("Latency too high");
                array_push($this->failureArray, $element);
                //unset($elements[$index]);
                continue;
            }

            if ((int)$element->getNumSuccess() !== 0 && ($element->getNumFailed() / $element->getNumSuccess()) > .8) {
                $element->setStatus(TorElement::STATUS_ERR);
                $element->setLastError("Error Ratio");
                array_push($this->failureArray, $element);
                //unset($elements[$index]);
                continue;
            }
            $element->setLastError(null);
            $element->setStatus(TorElement::STATUS_OK);
        }


    }


    private function preCheck()
    {
        $cache = Helper::getCache();
        $cache = $cache->get(TorConfig::SAVE_ARRAY);
        if (!is_array($cache) || count($cache) === 0) {
            echo "Cache not reachable\n";
            return false;
        }
        $cmd = `echo 'g73zwt45x23c94t' | sudo -S ps -A |grep tor`;
        if (null === $cmd) {
            echo "PS Command return null\n";
            return false;
        }
        return true;
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function restart()
    {

        $dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../../');

        //Not neccecary but okay...
        (Process::fromShellCommandline("sudo -S killall " . TorConfig::TOR_CALL))->run();
        (Process::fromShellCommandline("sudo -S " . TorConfig::WORKING_DIR))->run();

        //Clear Cache
        $cache = Helper::getCache();
        $cache->delete(TorConfig::SAVE_ARRAY);

        //Restart TOr and exit. Next time we will see ...
        $restartCmd = "sudo php " . $dir . "/main.php torano:tor " . TorConfig::DEFAULT_NUM_SERVERS;

        try {
            $process = Process::fromShellCommandline($restartCmd);
            $process->run(function ($type, $buffer) use ($process) {
                if (Process::ERR === $type) {
                    echo 'ERR > ' . $buffer;
                } else {
                    echo 'OUT > ' . $buffer;
                    if ($buffer === TorConfig::FINISH_STRING) {
                        $process->stop(0, 9);
                    }
                }
            });
        } catch (ProcessTimedOutException $timeout) {
            if ($process->isSuccessful()) {
                $this->out->writeln("Restarted...");
                exit;
            } else {
                echo "Process failed!\n";
                exit;
            }
        }


    }





    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->out = $output;
        $baseCache = Helper::getCache();





        if (!$this->preCheck()) {
            $this->out->writeln("<error>ERROR. Torano has not been initialized</error>");
            exit;//$this->restart();
        } else {
            $this->out->writeln("<info>Precheck passed, going on</info>");
        }
        $this->proxyArray = $baseCache->get(TorConfig::SAVE_ARRAY);
        $this->updateInformations($this->proxyArray);
        $this->checkInformations($this->proxyArray);
        //$baseCache->set(TorConfig::SAVE_ARRAY, $this->proxyArray, TorConfig::SAVE_TIME);
        Helper::displayToranos($output, $this->proxyArray);
        //$this->restartFailed();

        echo "\n";
        return 1;
    }


    private function restartFailed()
    {
        foreach ($this->failureArray as $torElement) {
            $this->restartPort($torElement->getPort());
        }
    }


    private function restartPort($port)
    {
        $this->out->writeln("<info>Restart Port\t" . $port . "</info>");
        $port = ($port - TorConfig::STARTING_SOCKS) + TorConfig::STARTING_SOCKS_CONTROL;
        $command = `printf "AUTHENTICATE \"password\"\r\nSIGNAL NEWNYM\r\n" | nc 127.0.0.1 $port > /dev/null 2>&1 &`;
        echo $command . "\n\n";
        $this->out->writeln("<info>Waiting</info>");
        sleep(5);
        return true;

    }


}
