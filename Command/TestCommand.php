<?php

namespace Abc\AnnBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Abc\AnnBundle\Entity\Network;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('ann:test')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $img = imagecreatefrompng('http://icons.iconarchive.com/icons/designbolts/turbo-movie-2013/128/Burn-Snail-icon.png');

        // $rValues = [];
        // $gValues = [];
        // $bValues = [];

        // for($x=1;$x<imagesx($img);$x++)
        // {
        //     for($y=1;$y<imagesy($img);$y++)
        //     {
        //         $rgb = imagecolorat($img, $x, $y);
        //         $r = ($rgb >> 16) & 0xFF;
        //         $g = ($rgb >> 8) & 0xFF;
        //         $b = $rgb & 0xFF;

        //         $rValues[$x][$y] = $r;
        //         $gValues[$x][$y] = $g;
        //         $bValues[$x][$y] = $b;
        //     }
        // }

        $network = new Network([2, 2, 1]);
        $network->setName('MLP');
        $network->setLearningRate(0.5);

        $wins = 0;
        $start = microtime(true);

        for ($i=0; $i < 1000; $i++) {
            $food['inputs'][0]  = mt_rand(0, 1);
            $food['inputs'][1]  = mt_rand(0, 1);

            $food['targets'][0] = 0;
            if (
                $food['inputs'][0] === 1 &&
                $food['inputs'][1] === 1
            ) {
                $food['targets'][0] = 1;
            }

            $network->train($food);

            $output->writeln('training... epochs: '.$network->getAge());

            $inputs = $network->getInputs();
            $outputs = $network->getOutputs();

            foreach ($outputs as $k => $v) {
                $poop[$k] = round($v);
            }

            $output->writeln('<info>inputs: '.$inputs[0].', '.$inputs[1].'</info>');

            if ($poop == $food['targets']) {
                $wins++;
                $output->writeln('<question>outputs: '.round($outputs[0], 2).'</question>');
            } else {
                $output->writeln('<error>outputs: '.round($outputs[0], 2).'</error>');
            }

            $winRate = 100 * $wins / ($i + 1);
            $output->writeln('success rate: '.round($winRate, 2).'%');
            $output->writeln('====================');
        }

        $end = microtime(true) - $start;
        $output->writeln('exec time: '.round($end).' sec');
    }
}
