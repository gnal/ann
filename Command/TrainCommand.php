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
        /*
            0: coffee
            1: donuts
            2: hamburger
        */
        $data = [
            [
                'input' => $this->getImageGreyscaleFlattenedValues('http://icons.iconarchive.com/icons/pixelkit/tasty-bites/16/coffee-icon.png'),
                'output' => [1,0,0]
            ],
            [
                'input' => $this->getImageGreyscaleFlattenedValues('http://icons.iconarchive.com/icons/pixelkit/tasty-bites/16/donuts-icon.png'),
                'output' => [0,1,0]
            ],
            [
                'input' => $this->getImageGreyscaleFlattenedValues('http://icons.iconarchive.com/icons/pixelkit/tasty-bites/16/hamburger-icon.png'),
                'output' => [0,0,1]
            ],
        ];

        $network = new Network([256, 16, 3]);
        $network->setName('MLP');
        $network->setLearningRate(0.5);

        // $network = $this->getContainer()->get('doctrine')
        //     ->getRepository('AbcAnnBundle:Network')
        //     ->find(4);

        $network->train($data, ['iterations' => 100000], $output);

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
