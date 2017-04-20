<?php

namespace tes\CmsBuilder\Command;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Mysql\Mysql;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tes\CmsBuilder\Config;

class ConfigFiles extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
      $this
          ->setName('config-files')
          ->setDescription('Rebuild configuration files replacing docker variables');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
      // Run post build commands.
      $config_files = Config::get('config_files') ?: [];

      $nginx_container_name = Compose::getContainerName(Platform::projectName(), 'nginx');
      $detect_port_command = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"80/tcp\") 0).HostPort}}' $nginx_container_name";
      foreach ($config_files as $source => $destination) {
          $placeholders = [
              '{{ salt }}' => hash('sha256', serialize($_SERVER)),
              '{{ nginx_container }}' => $nginx_container_name,
              '{{ mariadb_container }}' => Compose::getContainerName(Platform::projectName(), 'mariadb'),
              '{{ redis_container }}' => Compose::getContainerName(Platform::projectName(), 'redis'),
              '{{ solr_container }}' => Compose::getContainerName(Platform::projectName(), 'solr'),
              '{{ external_project_domain }}' => 'http://localhost/' . trim(shell_exec($detect_port_command)),
              '{{ mysql_user }}' => Mysql::getMysqlUser(),
              '{{ mysql_password }}' => Mysql::getMysqlPassword(),
              '{{ project_root }}' => Platform::rootDir(),
          ];
          $source = Platform::rootDir() . '/' . $source;
          if (!is_file($source)) {
              $output->writeln("<error>Unable to read configuration file: $source</error>");
              continue;
          }
          $file_content = file_get_contents($source);
          $new_file_content = str_replace(array_keys($placeholders), array_values($placeholders), $file_content);
          file_put_contents(Platform::rootDir() . '/' . $destination, $new_file_content);
      }
  }

}
