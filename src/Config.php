<?php

namespace tes\CmsBuilder;

use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\YamlConfigReader;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads and writes .cms-builder.yml configuration files.
 */
class Config
{
    use YamlConfigReader;

    protected function getConfigFilePath()
    {
        return '.cms-builder.yml';
    }

    /**
     * Sets a configuration value for the specified key.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public static function set($key, $value)
    {
        return self::instance()->setConfig($key, $value);
    }

    /**
     * Sets a configuration value for the specified key.
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Writes a file to a directory. The default directory is the platform project root directory.
     *
     * @param string $destinationDir
     *
     * @return $this
     */
    public static function write($destinationDir = null)
    {
        return self::instance()->writeConfig($destinationDir);
    }

    /**
     * Writes a file to a directory. The default directory is the platform project root directory.
     *
     * @param string $destinationDir
     *
     * @return $this
     */
    public function writeConfig($destinationDir = null)
    {
        if (!$destinationDir) {
            $destinationDir = Platform::rootDir();
        }
        file_put_contents($destinationDir . '/' . $this->getConfigFilePath(), Yaml::dump($this->config, 2));
        return $this;
    }
}
