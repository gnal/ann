<?php

namespace Abc\AnnBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Abc\AnnBundle\Entity\Layer;

/**
 * @ORM\Table(name="ann_network")
 * @ORM\Entity
 */
class Network
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Layer", mappedBy="network", cascade={"persist"})
     */
    protected $layers;

    /**
     * @ORM\Column(type="integer")
     */
    protected $age = 0;

    /**
     * @ORM\Column(type="float", name="learning_rate")
     */
    protected $learningRate = 0.5;

    protected $outputs;

    protected $inputs;

    protected $win;

    public function __construct(array $params)
    {
        $this->layers = new ArrayCollection();
        $i = 0;

        foreach ($params as $nbNeurons) {
            if ($i > 0) {
                $layer = new Layer($nbNeurons, $nbSynapses);
                $layer->setNetwork($this);
                $this->layers[] = $layer;
            }
            $nbSynapses = $nbNeurons;
            $i++;
        }
    }

    public function scale($n, $max)
    {
        return $n / $max;
    }

    public function run(array $inputs)
    {
        $this->inputs = $inputs;
        $l = $this->layers->count() - 1;
        $this->outputs = array();
        $ob = array();
        $i = 0;

        foreach ($this->layers as $layer) {
            foreach ($layer->getNeurons() as $neuron) {
                $output = $neuron->process($i === 0 ? $inputs : $ob[$i - 1]);
                $ob[$i][] = $output;
                if ($i === $l) $this->outputs[] = $output;
            }
            $i++;
        }
    }

    public function learn(array $targets)
    {
        $l = $this->layers->count() - 1;
        $errors = [];
        $j = 0;

        for ($i=$l; $i >= 0; $i--) {
            $errors[$i] = [];
            foreach ($this->layers[$i]->getNeurons() as $n => $neuron) {
                if ($i === $l) {
                    $delta = $neuron->calcDelta($targets[$j] - $neuron->getOutput());
                    $neuron->setDelta($delta);
                    $j++;
                } else {
                    $delta = $neuron->calcDelta($errors[$i + 1][$n]);
                    $neuron->setDelta($delta);
                }

                $errors[$i] = array_fill(0, $neuron->getSynapses()->count(), 0);

                foreach ($neuron->getSynapses() as $s => $synapse) {
                    $newWeight = $neuron->calcNewWeight($this->learningRate, $delta, $synapse->getWeight(), $synapse->getInput());
                    $newBias = $neuron->calcNewBias($this->learningRate, $delta);

                    $synapse->setWeight($newWeight);
                    $neuron->setBias($newBias);

                    $errors[$i][$s] += $delta * $newWeight;
                }
            }
        }
        $this->age++;
    }

    protected function mse($targets)
    {
        $total = 0;
        $i = 0;
        foreach ($targets as $target) {
            $error = $target - $this->getOutputs()[$i];
            $error = pow($error, 2);
            $total += $error;
        }
        return $total / count($targets);
    }

    public function train(array $data, array $options = [], $logger)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $options = $resolver->resolve($options);

        $start = microtime(true);
        $l = 0;
        $results = [];

        for ($i=0; $i < $options['iterations']/count($data); $i++) {
            foreach ($data as $value) {
                $this->run($value['input']);
                $this->learn($value['output']);
                // output stuff
                $logger->writeln('training... epochs: '.$this->getAge());

                foreach ($this->getOutputs() as $k => $v) {
                    $poop[$k] = intval(round($v));
                }

                $logger->writeln('<info>input: '.implode(' ', $value['input']).'</info>');
                if ($poop === $value['output']) {
                    $results[$l] = 1;
                    $logger->writeln('<question>output: '.implode(' ', $poop).'</question>');
                } else {
                    $results[$l] = 0;
                    $logger->writeln('<error>output: '.implode(' ', $poop).'</error>');
                }

                if (count($results) === 1000) {
                    $count = array_count_values($results);
                    $wins = isset($count[1]) ? $count[1] : 0;
                    $winRate = $wins/1000*100;
                    $logger->writeln('last 1000 win: '.$winRate.'%');
                }
                $logger->writeln('===');
                $l >= 999 ? $l = 0 : $l++;
                // end output stuff
            }
        }

        $end = microtime(true) - $start;
        $logger->writeln('exec time: '.round($end).' sec');
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'iterations' => 10000,
            'error_thresh' => 0.005,
        ]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function addLayer($layer)
    {
        $this->layers[] = $layer;

        return $this;
    }

    public function getLayers()
    {
        return $this->layers;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    public function getOutputs()
    {
        return $this->outputs;
    }

    public function setOutputs($outputs)
    {
        $this->outputs = $outputs;

        return $this;
    }

    public function getInputs()
    {
        return $this->inputs;
    }

    public function setInputs($inputs)
    {
        $this->inputs = $inputs;

        return $this;
    }

    public function getLearningRate()
    {
        return $this->learningRate;
    }

    public function setLearningRate($learningRate)
    {
        $this->learningRate = $learningRate;

        return $this;
    }
}
