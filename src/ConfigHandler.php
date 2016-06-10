<?php

/**
 * Created by PhpStorm.
 * User: sviluppo
 * Date: 09/05/16
 * Time: 14.49
 */
namespace SwissArmy;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

class ConfigHandler
{

    public static function loadConf($conf)
    {

        try {
            $config = Yaml::parse(@file_get_contents($conf));
            if (array_key_exists('imports', $config)) {
                foreach ($config['imports'] as $import) {
                    if (!empty($import['resource'])) {
                        if (substr($import['resource'], 0, 1) !== '/') {
                            $import['resource'] = dirname($conf) . '/' . $import['resource'];
                        }
                        $partial = Yaml::parse(@file_get_contents($import['resource']));
                        $config = array_merge_recursive($config, $partial);
                    }
                }
            }
            if (empty($config)) {
                throw new ParseException($conf.' is empty or not exist');
            }
        } catch (ParseException $e) {
            echo 'Config does not exist!' . PHP_EOL;
        }
        return $config;
    }
}
