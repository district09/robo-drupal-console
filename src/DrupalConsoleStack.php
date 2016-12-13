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
class DrupalConsoleStack extends CommandStack {

  use CommandArguments;

  /**
   * Verbosity levels:
   * 1 for normal output, 2 for more verbose output, and 3 for debug.
   */
  const VERBOSITY_LEVEL_NORMAL = 1;
  const VERBOSITY_LEVEL_VERBOSE = 2;
  const VERBOSITY_LEVEL_DEBUG = 3;

  /**
   * Pass arguments to the executable; applies to the next command only.
   *
   * @var string
   */
  protected $argumentsForNextCommand;


  /**
   * The Drupal console version.
   *
   * @var string
   */
  protected $drupalConsoleVersion;


  /**
   * Drush site alias. We need to save this, since it needs to be the first
   * argument.
   *
   * @var string
   */
  protected $siteAlias;

  /**
   * Creates a DrupalConsoleStack object.
   *
   * @param string $pathToDrupalConsole
   *   The path to the Drupal Console executable.
   */
  public function __construct($pathToDrupalConsole = 'drupal') {
    $this->executable = $pathToDrupalConsole;
  }

  /**
   * Pass argument to executable; applies to the next command only.
   *
   * @param string $arg
   *   The argument to pass.
   *
   * @return $this
   */
  protected function argForNextCommand($arg) {
    return $this->argsForNextCommand($arg);
  }

  /**
   * Pass methods parameters as arguments to executable;
   * applies to the next command only.
   *
   * @param mixed $args,...
   *   If it's an array, each element is an argument, if not, each parameter to
   *   this method is an argument.
   *
   * @return $this
   */
  protected function argsForNextCommand($args) {
    if (!is_array($args)) {
      $args = func_get_args();
    }
    $this->argumentsForNextCommand .= " " . implode(' ', $args);
    return $this;
  }

  /**
   * Sets the Drupal root directory option.
   *
   * @param string $drupalRootDirectory
   *   The path to the Drupal root directory.
   *
   * @return $this
   */
  public function drupalRootDirectory($drupalRootDirectory) {
    $this->printTaskInfo('Drupal root: <info>' . $drupalRootDirectory . '</info>');
    $this->option('--root', $drupalRootDirectory);

    return $this;
  }

  /**
   * Sets the URI of the Drupal site.
   *
   * @param string $uri
   *   URI of the Drupal site to use (for multi-site environments or when
   *   running on an alternate port).
   *
   * @return $this
   */
  public function uri($uri) {
    $this->printTaskInfo('URI: <info>' . $uri . '</info>');
    $this->option('--uri', $uri);

    return $this;
  }

  /**
   * Sets the environment name.
   *
   * @param string $environment
   *   The environment name.
   *
   * @return $this
   */
  public function environment($environment = 'prod') {
    $this->printTaskInfo('Environment: <info>' . $environment . '</info>');
    $this->option('--env ', $environment);

    return $this;
  }

  /**
   * Switches off debug mode.
   *
   * @return $this
   */
  public function noDebug() {
    $this->option('--no-debug');

    return $this;
  }

  /**
   * Sets the verbosity level.
   *
   * @param int $level
   *   One of the DrupalConsoleStack::VERBOSITY_LEVEL_* constants.
   *
   * @return $this
   */
  public function verbose($level = static::VERBOSITY_LEVEL_NORMAL) {
    $this->option('--verbose', $level);

    return $this;
  }

  /**
   * Sets the site name.
   *
   * @param string $siteName
   *   The site name.
   *
   * @return $this
   */
  public function siteName($siteName) {
    $this->argForNextCommand('--site-name=' . escapeshellarg($siteName));

    return $this;
  }

  /**
   * Sets the site mail.
   *
   * @param string $siteMail
   *   The site mail.
   *
   * @return $this
   */
  public function siteMail($siteMail) {
    $this->argForNextCommand('--site-mail=' . $siteMail);

    return $this;
  }

  /**
   * Sets the name of directory under 'sites' which should be created. Only
   * needed when the subdirectory does not already exist. Defaults to 'default'.
   *
   * @param string $sitesSubdir
   *   The name of the subdirectory.
   *
   * @return $this
   */
  public function sitesSubdir($sitesSubdir) {
    $this->argForNextCommand('--sites-subdir=' . $sitesSubdir);

    return $this;
  }

  /**
   * Sets the default site language.
   *
   * @param string $locale
   *   The language code.
   *
   * @return $this
   */
  public function locale($locale) {
    $this->argForNextCommand('--locale=' . $locale);

    return $this;
  }

  /**
   * Sets the e-mail address for the account with uid 1.
   *
   * @param string $accountMail
   *   The e-mail address for the account with uid 1.
   *
   * @return $this
   */
  public function accountMail($accountMail) {
    $this->argForNextCommand('--account-mail=' . $accountMail);

    return $this;
  }

  /**
   * Sets the username for the account with uid 1.
   *
   * @param string $accountName
   *   The username.
   *
   * @return $this
   */
  public function accountName($accountName) {
    $this->argForNextCommand('--account-name=' . escapeshellarg($accountName));

    return $this;
  }

  /**
   * Sets the password for the account with uid 1.
   *
   * @param string $accountPass
   *   The password.
   *
   * @return $this
   */
  public function accountPass($accountPass) {
    $this->argForNextCommand('--account-pass=' . $accountPass);

    return $this;
  }

  /**
   * Sets the table prefix to use for initial install.
   *
   * @param string $dbPrefix
   *   The table prefix.
   *
   * @return $this
   */
  public function dbPrefix($dbPrefix) {
    $this->argForNextCommand('--db-prefix=' . $dbPrefix);

    return $this;
  }

  /**
   * Returns the Drupal console version.
   *
   * @return string
   *   The Drupal console version.
   */
  public function getVersion() {
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
  public function siteStatus() {
    return $this->drupal('site:status');
  }

  /**
   * Clears the given cache.
   *
   * @param string $cacheName
   *   The cache name.
   *
   * @return $this
   */
  public function cacheRebuild($cacheName = 'all') {
    $this->printTaskInfo('Cache rebuild');

    return $this->drupal("cache:rebuild $cacheName");
  }

  /**
   * Execute a specific Update N function in a module, or execute all.
   *
   * @param string $module
   *   The module name.
   * @param string $updateN
   *   Specific update N function to be executed.
   *
   * @return $this
   */
  public function updateDb($module = 'all', $updateN = '') {
    $this->printTaskInfo('Perform database updates');
    $this->drupal("update:execute $module $updateN");

    return $this;
  }

  /**
   * Sets the maintenance mode.
   *
   * @param bool $mode
   *   Whether or not the maintenance mode should be on or off.
   *
   * @return $this
   */
  public function maintenance($mode = TRUE) {
    $maintenanceMode = $mode ? 'on' : 'off';
    $this->printTaskInfo("Set maintenance mode $maintenanceMode");

    return $this->drupal('site:maintenance $maintenanceMode');
  }

  /**
   * Executes `drupal site:install`.
   *
   * @param string $installationProfile
   *   The installation profile to use during install.
   *
   * @return $this
   */
  public function siteInstall($installationProfile = '') {
    return $this->drupal('site:install ' . $installationProfile);
  }

  /**
   * Runs the given Drupal console command.
   *
   * @param string $command
   *   The Drupal console command to execute.
   * @param bool $assumeYes
   *   Whether or not to assume yes on all prompts.
   *
   * @return $this
   */
  public function drupal($command, $assumeYes = true) {
    return $this->exec($this->injectArguments($command, $assumeYes));
  }

  /**
   * Appends arguments to the command.
   *
   * @param string $command
   *   The command to append the arguments to.
   * @param bool $assumeYes
   *   Whether or not to assume yes on all prompts.
   *
   * @return string
   *   The modified command string.
   */
  protected function injectArguments($command, $assumeYes) {
    $cmd = $command . ($assumeYes ? ' --yes' : '') . $this->arguments . $this->argumentsForNextCommand;
    $this->argumentsForNextCommand = '';

    return $cmd;
  }

}
