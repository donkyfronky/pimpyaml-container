<?php

namespace SwissArmy;

use Interop\Container\ContainerInterface;
use Pimple\Container;

class SimpleContainer extends Container implements ContainerInterface
{

    protected $parameters;
    protected $routes;


    public function __construct(array $values = [])
    {

        parent::__construct($values);
        $this->parameters = $values['parameters'];

        if (!empty($values['services'])) {
            $this->loadClasses($values['services']);
        }

    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new \Exception(sprintf('Identifier "%s" is not defined.', $id));
        }
            return $this->offsetGet($id);
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($nameV)
    {
        if (!array_key_exists($nameV, $this->parameters)) {
            throw new \Exception('Parameter \'' . $nameV . '\' not exist' . PHP_EOL);
        }
        return $this->parameters[$nameV];

    }

    protected function parsingArgument($argument)
    {

        if (is_array($argument)) {
            return $argument;
        }

        $container = $this;
        if (substr($argument, 0, 1) === '@') {
            if (strcmp('@container', $argument) === 0) {
                return $container;
            } else {
                return $container->get(str_replace('@', '', $argument));
            }
        }
        if (substr($argument, 0, 1) === '%') {
            return $this->getParameter(str_replace('%', '', $argument));
        }
        return $argument;
    }
    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }
    public function loadClasses(array $services)
    {
        $container = $this;
        foreach ($services as $name => $item) {
            if (empty($item['class'])) {
                throw new \Exception('Class cannot be empty!');
            }

            $container[$name] = function ($container) use ($item, $name) {

                $ref = new \ReflectionClass($item['class']);
                $args = [];

                if (!empty($item['arguments'])) {
                    foreach ($item['arguments'] as $key => $argument) {
                        $args[] = $this->parsingArgument($argument);
                    }
                }
                $obj = $ref->newInstanceArgs($args);
                if (!empty($item['calls'])) {
                    foreach ($item['calls'] as $call) {
                        $method = $call[0];
                        $methods_argument = $call[1];
                        $arg = [];

                        foreach ($methods_argument as $argument) {
                            $arg[] = $argument;
                        }

                        $met = $ref->getMethod($method);
                        $met->invokeArgs($obj, $arg);
                    }
                }
                return $obj;
            };
        }
    }
}
