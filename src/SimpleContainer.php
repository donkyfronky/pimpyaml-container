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

  protected function getParameterRecursive(array $data, $param)
  {

    $aParam = explode('.', $param);
    if (count($aParam) == 1) {

      if (!array_key_exists($param, $data)) {
        throw new \Exception('Parameter not exist');
      }

      return $this->formatFromType($data[$param]);
    }

    return $this->getParameterRecursive($data[$aParam[0]], implode('.', array_slice($aParam, 1)));
  }

  public function formatFromType($parameter)
  {

    switch ($parameter) {
      case (strcmp($parameter,'true')===0 || strcmp($parameter,'false')===0):
        return ($parameter === 'true');
        break;
      case strcmp($parameter,'null')===0:
        return null;
        break;
      case is_numeric($parameter):
        switch ($parameter) {
          case strpos($parameter,'.')!==0:
            return floatval($parameter);
            break;
          case strpos($parameter,'.')===0:
            return intval($parameter);
            break;
        }
      case is_string($parameter):
        return (string)$parameter;
        break;
      case (array)$parameter:
        return boolval($parameter);
        break;
    }

  }

  public function getParameter($nameV)
  {
    return $this->getParameterRecursive($this->parameters, $nameV);
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

              $arg[] = $this->parsingArgument($argument);
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
