<?php

/**
 * Planet configuration class
 */
class PlanetConfig
{

    public $conf;

    public function __construct($array)
    {
        $defaultConfig = array(
            'url'           => 'http://www.example.com/',
            'name'          => '',
            'locale'        => 'en',
            'items'         => 10,
            'refresh'       => 240,
            'cache'         => 10,
            'cachedir'      => './cache',
            'storage'       => 'sqlite'
        );

        // User config
        $this->conf = $array;

        // Complete config with default config
        foreach ($defaultConfig as $key => $value) {
            if (!isset($this->conf[$key])) {
                $this->conf[$key] = $value;
            }
        }
    }

    public function getUrl()
    {
        return $this->conf['url'];
    }

    public function getName(){
        return $this->conf['name'];
    }

    public function setName($name)
    {
        $this->conf['name'] = $name;
    }

    public function getCacheTimeout()
    {
        return $this->conf['refresh'];
    }

    public function getOutputTimeout()
    {
        return $this->conf['cache'];
    }

    public function getMaxDisplay()
    {
        return $this->conf['items'];
    }

    public function getCategories()
    {
        return $this->conf['categories'];
    }

    public function getStorage()
    {
        return $this->conf['storage'];
    }

    public function toYaml()
    {
        return Spyc::YAMLDump($this->conf,4);
    }

    /**
     * Generic accessor for config.
    */
    public function __get($key)
    {
        $key = strtolower($key);

        return array_key_exists($key, $this->conf) ?
            $this->conf[$key] :
            null;
    }
}
