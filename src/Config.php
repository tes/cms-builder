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
    /**
     * If there a multiple .cms-builder.*.yml the choose site.
     *
     * @see \tes\CmsBuilder\Application::chooseSite()
     *
     * @var string
     */
    protected static $site;

    use YamlConfigReader;

    /**
     * Sets a site to build.
     *
     * @param $site
     */
    public static function setSite($site) {
        static::$site = $site;
    }

    /**
     * Gets the site being built.
     */
    public static function getSite() {
        return static::$site;
    }

    /**
     * Fimnds sites based on .cms-builder.*.yml files.
     *
     * @return array
     */
    public static function findSites() {
        $sites = [];
        $files = new \DirectoryIterator(Platform::rootDir());
        $files = new \RegexIterator($files, '/^\.cms-builder\.[^\.]*\.yml$/');
        foreach ($files as $file) {
            preg_match('/^\.cms-builder\.([^\.]*)\.yml$/', $file->getFileName(), $matches);
            $sites[] =  $matches[1];
        }
        return $sites;
    }

    protected function getConfigFilePath()
    {
        $root = Platform::rootDir();

        $file = '.cms-builder.yml';
        if (static::$site) {
            $file = '.cms-builder.' . static::$site . '.yml';
        }
        if (!file_exists($root . '/' . $file)) {
            throw new \RuntimeException("$root/$file does not exist");
        }

        return $file;
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
