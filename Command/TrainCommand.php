<?php

namespace Abc\AnnBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Abc\AnnBundle\Entity\Network;

class TrainCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ann:train')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numbers[1] = [
            0,0,1,0,0,
            0,0,1,0,0,
            0,0,1,0,0,
            0,0,1,0,0,
            0,0,1,0,0,
        ];
        $numbers[2] = [
            1,1,1,1,1,
            0,0,0,1,0,
            0,0,1,0,0,
            0,1,0,0,0,
            1,1,1,1,1,
        ];
        $numbers[3] = [
            1,1,1,1,1,
            0,0,0,0,1,
            0,1,1,1,1,
            0,0,0,0,1,
            1,1,1,1,1,
        ];
        $numbers[4] = [
            1,0,0,1,0,
            1,0,0,1,0,
            1,1,1,1,0,
            0,0,0,1,0,
            0,0,0,1,0,
        ];
        $numbers[5] = [
            1,1,1,1,1,
            0,1,0,0,0,
            0,0,1,0,0,
            0,0,0,1,0,
            1,1,1,1,1,
        ];

        $data = [
            ['input' => [0, 1], 'output' => [1]],
            ['input' => [0, 0], 'output' => [0]],
            ['input' => [1, 1], 'output' => [0]],
            ['input' => [1, 0], 'output' => [1]],
        ];

        $network = new Network([2, 4, 1]);
        $network->setName('MLP');
        $network->setLearningRate(0.3);

        $network->train($data, [], $output);

        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($network);
        $em->flush();
    }

    protected function getImageGreyscaleFlattenedValues($filename)
    {
        $img = imagecreatefrompng($filename);

        $rValues = [];
        $gValues = [];
        $bValues = [];

        for($x=0;$x<imagesx($img);$x++)
        {
            for($y=0;$y<imagesy($img);$y++)
            {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $rValues[$x][$y] = $r ? 1 : 0;
                $gValues[$x][$y] = $g ? 1 : 0;
                $bValues[$x][$y] = $b ? 1 : 0;
            }
        }

        foreach ($rValues as $value) {
            foreach ($value as $val) {
                $flattenedValues[] = $val;
            }
        }

        return $flattenedValues;
    }

    protected function getImageRgbFlattenedValues($filename)
    {
        $img = imagecreatefrompng($filename);

        $rValues = [];
        $gValues = [];
        $bValues = [];

        for($x=0;$x<imagesx($img);$x++)
        {
            for($y=0;$y<imagesy($img);$y++)
            {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $rValues[$x][$y] = $r;
                $gValues[$x][$y] = $g;
                $bValues[$x][$y] = $b;
            }
        }

        foreach ($rValues as $value) {
            foreach ($value as $val) {
                $flattenedValues[] = $val;
            }
        }

        foreach ($gValues as $value) {
            foreach ($value as $val) {
                $flattenedValues[] = $val;
            }
        }

        foreach ($bValues as $value) {
            foreach ($value as $val) {
                $flattenedValues[] = $val;
            }
        }

        return $flattenedValues;
    }
}
