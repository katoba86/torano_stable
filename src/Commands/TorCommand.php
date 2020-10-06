<?php

namespace Commands;

use Classes\Helper;
use Classes\Torano\TorConfig;
use Classes\Torano\TorElement;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;


class TorCommand extends Command
{

    /**
     * @var null|OutputInterface
     */
    private $out = null;
    private $numServer = 10;


    protected $proxyUrls = [];


    private $country = 'DE';
    private $noKill = false;


    /**
     * @var null|Cache
     */
    protected $cache = null;


    protected function configure()
    {
        $this->setName('torano:tor');
        $this->addArgument('num', InputArgument::REQUIRED, 'Num Tor Server');
        $this->addArgument('country', InputArgument::OPTIONAL, 'COUNTRY - if set - toranos wont be reset');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->numServer = (int)$input->getArgument('num');
        $this->out = $output;
        if ($input->getArgument('country') !== null) {
            $this->country = strtoupper($input->getArgument('country'));
            $this->noKill = true;
        }


        $this->cache = Helper::getCache();


        if (!$this->noKill) {
            (Process::fromShellCommandline("killall " . TorConfig::TOR_CALL))->run();
            (Process::fromShellCommandline("rm -rf " . TorConfig::WORKING_DIR))->run();
            $inc = 0;
        } else {
            $this->proxyUrls = $this->cache->get(TorConfig::SAVE_ARRAY);
            $inc = $this->getStartingPort();
        }


        $this->createDirs();

        for ($i = 0; $i < $this->numServer; $i++) {

            $this->createTorConfig($i + $inc);
            $this->startTorInstance($i + $inc);

        }

        $this->cache->set(TorConfig::SAVE_ARRAY, $this->proxyUrls, TorConfig::SAVE_TIME);
        return 1;
    }


    public function startTorInstance($i, $returnElement = false)
    {


        $name = str_replace(":id", $i, TorConfig::TOR_CONFIG_NAME);
        $filename = TorConfig::WORKING_DIR . DIRECTORY_SEPARATOR . TorConfig::TOR_CFG . $name;
        $startcmd = "cd " . TorConfig::WORKING_DIR . " && " . TorConfig::TOR_CALL . " " . str_replace(":config", $filename, TorConfig::TOR_PARAMS);


        $port = (int)(TorConfig::STARTING_SOCKS + $i);


        $process = Process::fromShellCommandline($startcmd);
        $process->setTimeout(60);
        $process->run(function ($type, $buffer) use ($process){
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {

                echo 'OUT > '.$buffer;

            }
        });


        if ($process->isSuccessful()) {
            $pid = Helper::getPidForPort($port);
             $process->stop();
        } else {
            echo "Process failed!\n";
            return false;
        }


        if (!$returnElement) {
            $this->proxyUrls[$i] = (new TorElement())
                ->setUrl("localhost:" . $port)
                ->setPort($port)
                ->setCountry($this->country)
                ->setCreated(time())
                ->setPid($pid);
        } else {
            return (new TorElement())
                ->setUrl("localhost:" . $port)
                ->setPort($port)
                ->setCountry($this->country)
                ->setCreated(time())
                ->setPid($pid);
        }

        return true;
    }


    private function createDirs()
    {
        shell_exec("mkdir -p " . TorConfig::WORKING_DIR);
        shell_exec("mkdir -p " . TorConfig::WORKING_DIR . DIRECTORY_SEPARATOR . TorConfig::TOR_CFG);
        shell_exec("mkdir -p " . TorConfig::WORKING_DIR . DIRECTORY_SEPARATOR . TorConfig::TOR_DATA);
        shell_exec("chmod -R a+rwx ".TorConfig::WORKING_DIR);
    }

    public function createTorConfig($i)
    {

        $dir = TorConfig::WORKING_DIR . DIRECTORY_SEPARATOR . TorConfig::TOR_DATA . str_replace(":id", $i, TorConfig::TOR_SERVER_DIR);

        $content = implode("\n", TorConfig::$TOR_CONFIG_TEMPLATE);
        $content = str_replace(
            [
                ":control",
                ":socks",
                ":datadir",
                ':country',
            ],
            [
                TorConfig::STARTING_SOCKS_CONTROL + $i,
                TorConfig::STARTING_SOCKS + $i,
                $dir,
                strtolower($this->country)
            ],
            $content
        );

        $name = str_replace(":id", $i, TorConfig::TOR_CONFIG_NAME);
        $filename = TorConfig::WORKING_DIR . DIRECTORY_SEPARATOR . TorConfig::TOR_CFG . $name;

        file_put_contents($filename, $content);
        shell_exec("mkdir -p " . $dir);

    }

    private function getStartingPort(): ?int
    {

        $cmd = `sudo netstat -antp | grep tor | grep :95 | awk '{ print $4 }' | cut -d: -f2 | sort -r | head -n 1`;



        return (null === $cmd) ? null : ((int)$cmd + 1) - TorConfig::STARTING_SOCKS;

    }


}
