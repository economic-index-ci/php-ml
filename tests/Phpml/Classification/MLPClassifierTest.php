<?php

declare(strict_types=1);

namespace tests\Phpml\Classification;

use Phpml\Classification\MLPClassifier;
use Phpml\NeuralNetwork\Training\Backpropagation;
use Phpml\NeuralNetwork\Node\Neuron;
use PHPUnit\Framework\TestCase;

class MLPClassifierTest extends TestCase
{
    public function testMLPClassifierLayersInitialization()
    {
        $mlp = new MLPClassifier(2, [2], [0, 1]);

        $this->assertCount(3, $mlp->getLayers());

        $layers = $mlp->getLayers();

        // input layer
        $this->assertCount(3, $layers[0]->getNodes());
        $this->assertNotContainsOnly(Neuron::class, $layers[0]->getNodes());

        // hidden layer
        $this->assertCount(3, $layers[1]->getNodes());
        $this->assertNotContainsOnly(Neuron::class, $layers[1]->getNodes());

        // output layer
        $this->assertCount(2, $layers[2]->getNodes());
        $this->assertContainsOnly(Neuron::class, $layers[2]->getNodes());
    }

    public function testSynapsesGeneration()
    {
        $mlp = new MLPClassifier(2, [2], [0, 1]);
        $layers = $mlp->getLayers();

        foreach ($layers[1]->getNodes() as $node) {
            if ($node instanceof Neuron) {
                $synapses = $node->getSynapses();
                $this->assertCount(3, $synapses);

                $synapsesNodes = $this->getSynapsesNodes($synapses);
                foreach ($layers[0]->getNodes() as $prevNode) {
                    $this->assertContains($prevNode, $synapsesNodes);
                }
            }
        }
    }

    public function testBackpropagationLearning()
    {
        // Single layer 2 classes.
        $network = new MLPClassifier(2, [2], ['a', 'b'], 1000);
        $network->train(
            [[1, 0], [0, 1], [1, 1], [0, 0]],
            ['a', 'b', 'a', 'b']
        );

        $this->assertEquals('a', $network->predict([1, 0]));
        $this->assertEquals('b', $network->predict([0, 1]));
        $this->assertEquals('a', $network->predict([1, 1]));
        $this->assertEquals('b', $network->predict([0, 0]));
    }

    public function testBackpropagationLearningMultilayer()
    {
        // Multi-layer 2 classes.
        $network = new MLPClassifier(5, [3, 2], ['a', 'b']);
        $network->train(
            [[1, 0, 0, 0, 0], [0, 1, 1, 0, 0], [1, 1, 1, 1, 1], [0, 0, 0, 0, 0]],
            ['a', 'b', 'a', 'b']
        );

        $this->assertEquals('a', $network->predict([1, 0, 0, 0, 0]));
        $this->assertEquals('b', $network->predict([0, 1, 1, 0, 0]));
        $this->assertEquals('a', $network->predict([1, 1, 1, 1, 1]));
        $this->assertEquals('b', $network->predict([0, 0, 0, 0, 0]));
    }

    public function testBackpropagationLearningMulticlass()
    {
        // Multi-layer more than 2 classes.
        $network = new MLPClassifier(5, [3, 2], ['a', 'b', 4]);
        $network->train(
            [[1, 0, 0, 0, 0], [0, 1, 0, 0, 0], [0, 0, 1, 1, 0], [1, 1, 1, 1, 1], [0, 0, 0, 0, 0]],
            ['a', 'b', 'a', 'a', 4]
        );

        $this->assertEquals('a', $network->predict([1, 0, 0, 0, 0]));
        $this->assertEquals('b', $network->predict([0, 1, 0, 0, 0]));
        $this->assertEquals('a', $network->predict([0, 0, 1, 1, 0]));
        $this->assertEquals('a', $network->predict([1, 1, 1, 1, 1]));
        $this->assertEquals(4, $network->predict([0, 0, 0, 0, 0]));
    }

    /**
     * @expectedException \Phpml\Exception\InvalidArgumentException
     */
    public function testThrowExceptionOnInvalidLayersNumber()
    {
        new MLPClassifier(2, [], [0, 1]);
    }

    /**
     * @expectedException \Phpml\Exception\InvalidArgumentException
     */
    public function testThrowExceptionOnInvalidClassesNumber()
    {
        new MLPClassifier(2, [2], [0]);
    }

    /**
     * @param array $synapses
     *
     * @return array
     */
    private function getSynapsesNodes(array $synapses): array
    {
        $nodes = [];
        foreach ($synapses as $synapse) {
            $nodes[] = $synapse->getNode();
        }

        return $nodes;
    }
}
