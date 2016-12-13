<?php

namespace DigipolisGent\Robo\Task\DrupalConsole;

use Robo\Common\CommandArguments;
use Robo\Task\CommandStack;

/**
 * Runs Drupal Console commands in stack. You can use `stopOnFail()` to point that stack should be terminated on first fail.
 * You can define global options for all commands (like Drupal root and uri).
 * The option --yes is always set, as it makes sense in a task runner.
 *
 * ``` php
 * $this->taskDrupalConsoleStack()
 *     ->drupalRootDirectory('/var/www/html/some-site')
 *     ->uri('sub.example.com')
 *     ->maintenanceOn(true)
 *     ->updateDb()
 *     ->revertAllFeatures()
 *     ->maintenanceOff()
 *     ->run();
 * ```
 *
 * Example site install command:
 *
 * ``` php
 * $this->taskDrushStack()
 *   ->siteName('Site Name')
 *   ->siteMail('site-mail@example.com')
 *   ->locale('de')
 *   ->accountMail('mail@example.com')
 *   ->accountName('admin')
 *   ->accountPass('pw')
 *   ->dbPrefix('drupal_')
 *   ->sqliteDbUrl('sites/default/.ht.sqlite')
 *   ->disableUpdateStatusModule()
 *   ->siteInstall('minimal')
 *   ->run();
 * ```
 */
class DrupalConsoleStack extends CommandStack
{
  use CommandArguments;

  protected $argumentsForNextCommand;

  /**
   * Pass argument to executable; applies to the
   * next command only.
   *
   * @param $arg
   * @return $this
   */
  protected function argForNextCommand($arg)
  {
    return $this->argsForNextCommand($arg);
  }

  /**
   * Pass methods parameters as arguments to executable;
   * applies to the next command only.
   *
   * @param $args
   * @return $this
   */
  protected function argsForNextCommand($args)
  {
    if (!is_array($args)) {
      $args = func_get_args();
    }
    $this->argumentsForNextCommand .= " ".implode(' ', $args);
    return $this;
  }

  /**
   * Drush site alias.
   * We need to save this, since it needs to be the first argument.
   *
   * @var string
   */
  protected $siteAlias;

  /**
   * @var string
   */
  protected $drupalConsoleVersion;

  public function __construct($pathToDrupalConsole = 'drupal')
  {
    $this->executable = $pathToDrupalConsole;
  }

  public function drupalRootDirectory($drupalRootDirectory)
  {
    $this->printTaskInfo('Drupal root: <info>' . $drupalRootDirectory . '</info>');
    $this->option('--root', $drupalRootDirectory);

    return $this;
  }

  public function uri($uri)
  {
    $this->printTaskInfo('URI: <info>' . $uri . '</info>');
    $this->option('--uri', $uri);

    return $this;
  }

  public function environment($environment = 'prod')
  {
    $this->printTaskInfo('Environment: <info>' . $environment . '</info>');
    $this->option('--env ', $environment);

    return $this;
  }

  public function noDebug()
  {
    $this->option('--no-debug');

    return $this;
  }

  public function verbose()
  {
    $this->option('--verbose');

    return $this;
  }

  public function siteName($siteName)
  {
    $this->argForNextCommand('--site-name=' . escapeshellarg($siteName));

    return $this;
  }

  public function siteMail($siteMail)
  {
    $this->argForNextCommand('--site-mail=' . $siteMail);

    return $this;
  }

  public function sitesSubdir($sitesSubdir)
  {
    $this->argForNextCommand('--sites-subdir=' . $sitesSubdir);

    return $this;
  }

  public function locale($locale)
  {
    $this->argForNextCommand('--locale=' . $locale);

    return $this;
  }

  public function accountMail($accountMail)
  {
    $this->argForNextCommand('--account-mail=' . $accountMail);

    return $this;
  }

  public function accountName($accountName)
  {
    $this->argForNextCommand('--account-name=' . escapeshellarg($accountName));

    return $this;
  }

  public function accountPass($accountPass)
  {
    $this->argForNextCommand('--account-pass=' . $accountPass);

    return $this;
  }

  public function dbPrefix($dbPrefix)
  {
    $this->argForNextCommand('--db-prefix=' . $dbPrefix);

    return $this;
  }

  /**
   * Returns the drupal console version.
   *
   * @return string
   */
  public function getVersion()
  {
    if (empty($this->drupalConsoleVersion)) {
      $isPrinted = $this->isPrinted;
      $this->isPrinted = false;
      $result = $this->executeCommand($this->executable . ' --version');
      $output = $result->getMessage();
      $this->drupalConsoleVersion = 'unknown';
      if (preg_match('#[0-9.]+#', $output, $matches)) {
        $this->drupalConsoleVersion = $matches[0];
      }
      $this->isPrinted = $isPrinted;
    }

    return $this->drupalConsoleVersion;
  }

  /**
   * Executes `drupal site:status`
   *
   * @return $this
   */
  public function siteStatus()
  {
    return $this->drupal('site:status');
  }

  /**
   * Clears the given cache.
   *
   * @param string $name cache name
   * @return $this
   */
  public function cacheRebuild($cacheName = 'all')
  {
    $this->printTaskInfo('Cache rebuild');

    return $this->drupal("cache:rebuild $cacheName");
  }

  /**
   * Runs pending database updates.
   *
   * @param string $module
   * @param string $updateN
   * @return $this
   */
  public function updateDb($module = 'all', $updateN)
  {
    $this->printTaskInfo('Perform database updates');
    $this->drupal("update:execute $module $updateN");

    return $this;
  }

  /**
   * Sets the maintenance mode.
   *
   * @return $this
   */
  public function maintenance($mode = TRUE)
  {
    $maintenanceMode = $mode ? 'on' : 'off';
    $this->printTaskInfo("Set maintenance mode $maintenanceMode");

    return $this->drupal('site:maintenance $maintenanceMode');
  }

  /**
   * @param string $installationProfile
   * @return $this
   */
  public function siteInstall($installationProfile)
  {
    return $this->drupal('site:install ' . $installationProfile);
  }

  /**
   * Runs the given drupal console command.
   *
   * @param string $command
   * @param bool $assumeYes
   * @return $this
   */
  public function drupal($command, $assumeYes = true)
  {
    return $this->exec($this->injectArguments($command, $assumeYes));
  }

  /**
   * Appends arguments to the command.
   *
   * @param string $command
   * @param bool $assumeYes
   * @return string the modified command string
   */
  protected function injectArguments($command, $assumeYes)
  {
    $cmd = $command . ($assumeYes ? ' --yes' : '') . $this->arguments . $this->argumentsForNextCommand;
    $this->argumentsForNextCommand = '';

    return $cmd;
  }
}