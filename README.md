Pimpyaml is a simple configuration package for Pimple container. It implements [ContainerInterface](https://github.com/container-interop/container-interop), so you can switch easly with another [container](https://github.com/container-interop/container-interop#projects-implementing-containerinterface)

#Install
`composer require mascherucci/pimpyaml-container`

#Usage
You can obviously define the service in normal [way](https://github.com/silexphp/Pimple#defining-services)
or you can define with yaml `config.yml` file with symfony like syntax

##Defining Services
###Loading config file
```php
  $conf_file = __DIR__.'/config.yml';
  $config = \SwissArmy\ConfigHandler::loadConf($conf_file);
  $container = new \SwissArmy\SimpleContainer($config);
```

`ConfigHandler` support several `imports -> resource` if you want to separate domain of configuration as you can see below

```yaml
imports:
    - resource: dependency.yml
    - resource: routes.yml
customvalues:
    custom1: 1    
```
###The Services
```yaml
services:
    zone:
        class: \DateTimeZone
        arguments: ['America/Adak']
    time:
        class: \DateTime
        arguments: ['2016-01-01','@zone']
    time2
        class: \DateTime
        calls:
            - [setTimezone , ['@zone']]
    dummy
        class: \yourclass
        arguments: ['@container']
    dummy2
        class: \yourclass
        arguments: ['%custom1%']        
```
At moment the only markdown support, as you can see, is `@object`,`@container`(is a special word to pass the container),`%yourvariable%`
