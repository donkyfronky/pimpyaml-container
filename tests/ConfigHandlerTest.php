<?php
namespace SwissArmy\test;

class ConfigHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $config;

    public function setUp()
    {
        $conf_file = __DIR__.'/config.yml';
        $this->config = \SwissArmy\ConfigHandler::loadConf($conf_file);
    }

    public function testSettingsExist()
    {
        $this->assertEquals($this->config['customvalues']['dummy'], 1);
    }

    public function testClassDefinitionExist()
    {
        $this->assertArraySubset($this->config['services']['time'], [
            'class'=>'\DateTime',
            'arguments'=>['2016-01-01','@zone']
        ]);
    }
}
