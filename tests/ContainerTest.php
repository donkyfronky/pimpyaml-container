<?php
namespace SwissArmy\test;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;
    protected $container_config=[
        'settings'=>[],
        'parameters'=>[],
        'services'=>[]
    ];

    public function setUp()
    {
        $this->container = new \SwissArmy\SimpleContainer($this->container_config);
    }

    /**
     * Test `get()` throws error if item does not exist
     *
     * @expectedException \Exception
     */
    public function testGetWithValueNotFoundError()
    {
        $this->container->get('foo');
    }

    /**
     * Test `get()` throws something that is a ContainerExpception - typically a NotFoundException, when there is a DI
     * config error
     *
     * @expectedException \Exception
     */
    public function testGetWithDiConfigErrorThrownAsException()
    {
        $container = new \SwissArmy\SimpleContainer($this->container_config);
        $container['foo'] =
            function (\Interop\Container\ContainerInterface $container) {
                return $container->get('doesnt-exist');
            }
        ;
        $container->get('foo');
    }

    /**
     * Test `get()` recasts \InvalidArgumentException as ContainerInterop-compliant exceptions when an error is present
     * in the DI config
     *
     * @expectedException \Exception
     */
    public function testGetWithDiConfigErrorThrownAsInvalidArgumentException()
    {
        $container = new \SwissArmy\SimpleContainer($this->container_config);
        $container['foo'] =
            function (\Interop\Container\ContainerInterface $container) {
                return $container['doesnt-exist'];
            }
        ;
        $container->get('foo');
    }

    public function testClassTimeLoaded()
    {

        $conf_file = __DIR__.'/config.yml';
        $config = \SwissArmy\ConfigHandler::loadConf($conf_file);
        $container = new \SwissArmy\SimpleContainer($config);
        $this->assertEquals($container->zone->getName(), $config['services']['zone']['arguments'][0]);
        $this->assertEquals($container->time->getTimezone()->getName(), $config['services']['zone']['arguments'][0]);
        $this->assertEquals($container->time->format('Y-m-d'), $config['services']['time']['arguments'][0]);
    }
}
