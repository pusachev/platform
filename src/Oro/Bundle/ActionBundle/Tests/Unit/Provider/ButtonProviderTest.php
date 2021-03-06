<?php

namespace Oro\Bundle\ActionBundle\Tests\Unit\Provider;

use Oro\Bundle\ActionBundle\Button\ButtonContext;
use Oro\Bundle\ActionBundle\Button\ButtonInterface;
use Oro\Bundle\ActionBundle\Button\ButtonsCollection;
use Oro\Bundle\ActionBundle\Button\ButtonSearchContext;
use Oro\Bundle\ActionBundle\Extension\ButtonProviderExtensionInterface;
use Oro\Bundle\ActionBundle\Provider\ButtonProvider;
use Oro\Bundle\ActionBundle\Tests\Unit\Stub\StubButton;
use Oro\Bundle\TestFrameworkBundle\Test\Stub\CallableStub;

class ButtonProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ButtonProvider */
    protected $buttonProvider;

    /** @var ButtonProviderExtensionInterface[]|\PHPUnit_Framework_MockObject_MockObject[] */
    private $extensions = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->buttonProvider = new ButtonProvider();
    }

    public function testMatch()
    {
        $searchContext = $this->createMock(ButtonSearchContext::class);

        $button1 = $this->getButton();
        $button2 = $this->getButton();
        $button3 = $this->getButton();

        $extension1 = $this->extension('one');
        $extension1->expects($this->once())->method('find')->willReturn([$button1]);
        $extension2 = $this->extension('two');
        $extension2->expects($this->once())->method('find')->willReturn([$button2, $button3]);

        $collection = $this->buttonProvider->match($searchContext);
        $this->assertInstanceOf(ButtonsCollection::class, $collection);

        //checking correct mapping button => extension at collection
        $callable = $this->createMock(CallableStub::class);
        $callable->expects($this->at(0))
            ->method('__invoke')
            ->with(
                $this->identicalTo($button1),
                $this->identicalTo($extension1)
            )->willReturn($button1);

        $callable->expects($this->at(1))
            ->method('__invoke')
            ->with(
                $this->identicalTo($button2),
                $this->identicalTo($extension2)
            )->willReturn($button2);

        $callable->expects($this->at(2))
            ->method('__invoke')
            ->with(
                $this->identicalTo($button3),
                $this->identicalTo($extension2)
            )->willReturn($button3);

        $collection->map($callable);
    }

    /**
     * @param string $identifier
     * @return ButtonProviderExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function extension($identifier)
    {
        if (isset($this->extensions[$identifier])) {
            return $this->extensions[$identifier];
        }

        $this->extensions[$identifier] = $this->createMock(ButtonProviderExtensionInterface::class);

        $this->buttonProvider->addExtension($this->extensions[$identifier]);

        return $this->extensions[$identifier];
    }

    /**
     * @dataProvider findAllDataProvider
     *
     * @param array $input
     * @param array $output
     */
    public function testFindAll(array $input, array $output)
    {
        /** @var ButtonSearchContext $searchContext */
        $searchContext = $this->createMock(ButtonSearchContext::class);

        $this->extension('one')->expects($this->once())
            ->method('find')
            ->with($searchContext)
            ->willReturn($input);

        $this->assertEquals($output, $this->buttonProvider->findAll($searchContext));
    }

    /**
     * @return array
     */
    public function findAllDataProvider()
    {
        $button1 = $this->getButton(1);
        $button2 = $this->getButton(2);
        $button3 = $this->getButton(3);

        return [
            'no input' => [
                'input' => [],
                'output' => []
            ],
            'one button' => [
                'input' => [$button2],
                'output' => [$button2]
            ],
            'just ordered' => [
                'input' => [$button2, $button1, $button3],
                'output' => [$button1, $button2, $button3]
            ],
            'with same will be overridden' => [
                'input' => [$button3, $button3, $button2],
                'output' => [$button2, $button3]
            ]
        ];
    }

    /**
     * @param int $order
     * @return ButtonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getButton($order = 1)
    {
        return new StubButton(
            [
                'order' => $order,
                'templateData' => ['additionalData' => []],
                'buttonContext' => new ButtonContext()
            ]
        );
    }
}
