<?php

namespace Abc\AnnBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Abc\AnnBundle\Entity\Network;

class RunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ann:run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $images[] = [0,0,0,1,1,1,0,0,0]; // -
        $images[] = [0,1,0,1,1,1,0,1,0]; // +
        $images[] = [0,0,1,0,1,0,1,0,0]; // /
        $images[] = [1,0,1,0,1,0,1,0,1]; // x

        $network = $this->getContainer()->get('doctrine')
            ->getRepository('AbcAnnBundle:Network')
            ->find(2);

        $wins = 0;
        $start = microtime(true);

        for ($i=0; $i < 1; $i++) {
            // $rand = mt_rand(0, count($images) - 1);
            $food = [1,1,1,0,0,1,1,1,1];

            // if ($food === $images[0]) {
            //     $answer = [1,0,0,0];
            // }

            // if ($food === $images[1]) {
            //     $answer = [0,1,0,0];
            // }

            // if ($food === $images[2]) {
            //     $answer = [0,0,1,0];
            // }

            // if ($food === $images[3]) {
            //     $answer = [0,0,0,1];
            // }

            $network->run($food);

            $output->writeln('training... epochs: '.$network->getAge());

            $inputs = $network->getInputs();
            $outputs = $network->getOutputs();

            foreach ($outputs as $k => $v) {
                $poop[$k] = round($v);
            }

            $output->writeln('<info>inputs: '.implode(' ', $food).'</info>');

            $string = '';
            foreach ($outputs as $val) {
                $string .= round($val, 1).' ';
            }

            if (!empty($answer)) {
                if ($poop == $answer) {
                    $wins++;
                    $output->writeln('<question>outputs: '.$string.'</question>');
                } else {
                    $output->writeln('<error>outputs: '.$string.'</error>');
                }
            } else {
                $output->writeln('outputs: '.$string.'');
            }

            $winRate = 100 * $wins / ($i + 1);
            $output->writeln('success rate: '.round($winRate, 2).'%');
            $output->writeln('====================');
        }

        $end = microtime(true) - $start;
        $output->writeln('exec time: '.round($end).' sec');
    }
}
