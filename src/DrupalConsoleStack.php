<?php

namespace DigipolisGent\Robo\Task\DrupalConsole;

use Robo\Common\CommandArguments;
use Robo\Task\CommandStack;

/**
 * Runs Drupal Console commands in stack. You can use `stopOnFail()` to point that stack should be terminated on
 * first fail. You can define global options for all commands (like Drupal root and uri). The option --yes is always
 * set, as it makes sense in a task runner.
 *
 * ``` php
 * $this->taskDrupalConsoleStack()
 *     ->drupalRootDirectory('/var/www/html/some-site')
 *     ->uri('sub.example.com')
 *     ->maintenanceOn(true)
 *     ->updateDb()
 *     ->configImport()
 *     ->maintenanceOff()
 *     ->run();
 * ```
 *
 * Example site install command:
 *
 * ``` php
 * $this->taskDrushStack()
 *   ->siteName('Site Name Mysql')
 *   ->siteMail('site-mail2@example.com')
 *   ->langcode('fr')
 *   ->accountMail('mail2@example.com')
 *   ->accountName('admin-user')
 *   ->accountPass('passw')
 *   ->dbPrefix('drupal_')
 *   ->dbType('mysql')
 *   ->dbHost('localhost')
 *   ->dbName('testdb')
 *   ->dbUser('dbuser')
 *   ->dbPass('testdbpw')
 *   ->siteInstall('standard')
 *   ->run();
 * ```
 */
class DrupalConsoleStack extends CommandStack
{

    use CommandArguments {
        option as optionNoEqualSign;
    }

    /**
     * Verbosity levels:
     * 1 for normal output, 2 for more verbose output, and 3 for debug.
     */
    const VERBOSITY_LEVEL_NORMAL = 1;
    const VERBOSITY_LEVEL_VERBOSE = 2;
    const VERBOSITY_LEVEL_DEBUG = 3;

    /**
     * Options to pass to the executable.
     *
     * @var array
     */
    protected $optionsForNextCmd = [];


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
    public function __construct($pathToDrupalConsole = 'drupal')
    {
        $this->executable = $pathToDrupalConsole;
    }

    /**
     * Pass option to executable. Options are prefixed with `--` , value can be provided in second parameter.
     * Option values are automatically escaped.
     *
     * @param string $option
     *   The option name.
     * @param string $value
     *   The option value.
     *
     * @return $this
     */
    public function option($option, $value = null)
    {
        if (!is_null($option) && strpos($option, '-') !== 0) {
            $option = "--$option";
        }
        $this->arguments .= is_null($option) ? '' : " " . $option;
        $this->arguments .= is_null($value) ? '' : "=" . static::escape($value);
        return $this;
    }

    /**
     * Pass an option to the executable; applies to the next command only.
     *
     * @param string $name
     *   The name of the option to pass.
     * @param string|null $value
     *   The value for this option to pass (optional).
     *
     * @return $this
     */
    protected function optionForNextCommand($name, $value = null)
    {
        return $this->optionsForNextCommand([$name => $value]);
    }

    /**
     * Pass this method's parameters as options to the executable;
     * applies to the next command only.
     *
     * @param array $options
     *   An array of associative arrays where the array key is the option name
     *   and the value is the (unescaped) option value.
     *
     * @return $this
     */
    protected function optionsForNextCommand($options)
    {
        $this->optionsForNextCmd = $options + $this->optionsForNextCmd;

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
    public function drupalRootDirectory($drupalRootDirectory)
    {
        $this->printTaskInfo(
            'Drupal root: <info>{drupalRootDirectory}</info>',
            ['drupalRootDirectory' => $drupalRootDirectory]
        );
        $this->option('root', $drupalRootDirectory);

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
    public function uri($uri)
    {
        $this->printTaskInfo('URI: <info>{uri}</info>', ['uri' => $uri]);
        $this->option('uri', $uri);

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
    public function environment($environment = 'prod')
    {
        $this->printTaskInfo(
            'Environment: <info>{environment}</info>',
            ['environment' => $environment]
        );
        $this->option('env', $environment);

        return $this;
    }

    /**
     * Switches off debug mode.
     *
     * @return $this
     */
    public function noDebug()
    {
        $this->printTaskInfo('Debug: <info>off</info>');
        $this->option('no-debug');

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
    public function verbose($level = self::VERBOSITY_LEVEL_NORMAL)
    {
        $this->printTaskInfo(
            'Verbosity: <info>{verbosity}</info>',
            ['verbosity' => $level]
        );
        $this->option('verbose', $level);

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
    public function siteName($siteName)
    {
        $this->optionForNextCommand('site-name', $siteName);

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
    public function siteMail($siteMail)
    {
        $this->optionForNextCommand('site-mail', $siteMail);

        return $this;
    }

    /**
     * Sets the file to use e.g. for database dump/restore.
     *
     * @param string $file
     *   The file to use.
     *
     * @return $this
     */
    public function file($file)
    {
        $this->optionForNextCommand('file', $file);

        return $this;
    }

    /**
     * Sets the directory to use e.g. for config import/export.
     *
     * @param string $directory
     *   The directory to use.
     *
     * @return $this
     */
    public function directory($directory)
    {
        $this->optionForNextCommand('directory', $directory);

        return $this;
    }

    /**
     * Set the tar option e.g. for config:export.
     *
     * @return $this
     */
    public function tar()
    {
        $this->optionForNextCommand('tar');

        return $this;
    }

    /**
     * Sets the default site language.
     *
     * @param string $langcode
     *   The language code.
     *
     * @return $this
     */
    public function langcode($langcode)
    {
        $this->optionForNextCommand('langcode', $langcode);

        return $this;
    }

    /**
     * Sets the database type to use for initial install.
     *
     * @param string $dbType
     *   The database type.
     *
     * @return $this
     */
    public function dbType($dbType)
    {
        $this->optionForNextCommand('db-type', $dbType);

        return $this;
    }

    /**
     * Sets the database file to use for initial install.
     *
     * @param string $dbFile
     *   The database file.
     *
     * @return $this
     */
    public function dbFile($dbFile)
    {
        $this->optionForNextCommand('db-file', $dbFile);

        return $this;
    }

    /**
     * Sets the database host to use for initial install.
     *
     * @param string $dbHost
     *   The database host.
     *
     * @return $this
     */
    public function dbHost($dbHost)
    {
        $this->optionForNextCommand('db-host', $dbHost);

        return $this;
    }

    /**
     * Sets the database name to use for initial install.
     *
     * @param string $dbName
     *   The database name.
     *
     * @return $this
     */
    public function dbName($dbName)
    {
        $this->optionForNextCommand('db-name', $dbName);

        return $this;
    }

    /**
     * Sets the database user to use for initial install.
     *
     * @param string $dbUser
     *   The database user.
     *
     * @return $this
     */
    public function dbUser($dbUser)
    {
        $this->optionForNextCommand('db-user', $dbUser);

        return $this;
    }

    /**
     * Sets the database password to use for initial install.
     *
     * @param string $dbPass
     *   The database password.
     *
     * @return $this
     */
    public function dbPass($dbPass)
    {
        $this->optionForNextCommand('db-pass', $dbPass);

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
    public function dbPrefix($dbPrefix)
    {
        $this->optionForNextCommand('db-prefix', $dbPrefix);

        return $this;
    }

    /**
     * Sets the database port to use for initial install.
     *
     * @param string $dbPort
     *   The database port.
     *
     * @return $this
     */
    public function dbPort($dbPort)
    {
        $this->optionForNextCommand('db-port', $dbPort);

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
    public function accountMail($accountMail)
    {
        $this->optionForNextCommand('account-mail', $accountMail);

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
    public function accountName($accountName)
    {
        $this->optionForNextCommand('account-name', $accountName);

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
    public function accountPass($accountPass)
    {
        $this->optionForNextCommand('account-pass', $accountPass);

        return $this;
    }

    /**
     * Returns the Drupal console version.
     *
     * @return string
     *   The Drupal console version.
     */
    public function getVersion()
    {
        if (empty($this->drupalConsoleVersion)) {
            $isPrinted = $this->isPrinted;
            $this->isPrinted = false;
            $result = $this->executeCommand($this->executable.' --version');
            $output = $result->getMessage();
            $this->drupalConsoleVersion = 'unknown';
            if ($result->wasSuccessful() &&
                preg_match(
                    '#[0-9.]+#',
                    $output,
                    $matches
                )
            ) {
                $this->drupalConsoleVersion = $matches[0];
            }
            $this->isPrinted = $isPrinted;
        }

        return $this->drupalConsoleVersion;
    }

    /**
     * Rebuilds the given cache.
     *
     * @param string $cacheName
     *   The cache name.
     *
     * @return $this
     */
    public function cacheRebuild($cacheName = 'all')
    {
        $this->printTaskInfo('Cache rebuild');

        return $this->drupal('cache:rebuild '.static::escape($cacheName));
    }

    /**
     * Execute a specific update N function in a module, or execute all.
     *
     * @param string $module
     *   The module name.
     * @param string $updateN
     *   Specific update N function to be executed.
     *
     * @return $this
     */
    public function updateDb($module = 'all', $updateN = '')
    {
        $this->printTaskInfo('Perform database updates');
        $this->drupal(
            'update:execute '.static::escape(
                $module
            ).($updateN ? ' '.static::escape($updateN) : '')
        );

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
    public function maintenance($mode = true)
    {
        $maintenanceMode = $mode ? 'on' : 'off';
        $this->printTaskInfo(
            'Set maintenance mode: <info>{maintenanceMode}</info>',
            ['maintenanceMode' => $maintenanceMode]
        );
        $this->drupal('site:maintenance '.static::escape($maintenanceMode));

        return $this;
    }

    /**
     * Execute a cron implementation.
     *
     * @param string $module
     *   The module name.
     *
     * @return $this
     */
    public function executeCron($module)
    {
        $this->printTaskInfo('Execute cron');
        $this->drupal('cron:execute '.static::escape($module));

        return $this;
    }

    /**
     * Executes `drupal site:install`.
     *
     * @param string $installationProfile
     *   The installation profile to use during install.
     *
     * @return $this
     */
    public function siteInstall($installationProfile = '')
    {
        return $this->drupal(
            'site:install'.($installationProfile ? ' '.static::escape(
                $installationProfile
            ) : '')
        );
    }

    /**
     * Export configuration.
     *
     * @return $this
     */
    public function configExport()
    {
        $this->printTaskInfo('Export configuration');
        $this->drupal('config:export');

        return $this;
    }

    /**
     * Import configuration.
     *
     * @return $this
     */
    public function configImport()
    {
        $this->printTaskInfo('Import configuration');
        $this->drupal('config:import');

        return $this;
    }

    /**
     * Dumps the database.
     *
     * @param string $database
     *   The database name.
     *
     * @return $this
     */
    public function dbDump($database)
    {
        $this->printTaskInfo(
            'Dump database: <info>{database}</info>',
            ['database' => $database]
        );
        $this->drupal('database:dump '.static::escape($database));

        return $this;
    }

    /**
     * Restores the database.
     *
     * @param string $database
     *   The database name.
     *
     * @return $this
     */
    public function dbRestore($database)
    {
        $this->printTaskInfo(
            'Restore database: <info>{database}</info>',
            ['database' => $database]
        );
        $this->drupal('database:restore '.static::escape($database));

        return $this;
    }

    /**
     * Drops the database.
     *
     * @param string $database
     *   The database name.
     *
     * @return $this
     */
    public function dbDrop($database)
    {
        $this->printTaskInfo(
            'Drops database: <info>{database}</info>',
            ['database' => $database]
        );
        $this->drupal('database:drop '.static::escape($database));

        return $this;
    }

    /**
     * Executes migrations.
     *
     * @param array $migrationIds
     *   The migration ids.
     *
     * @return $this
     */
    public function executeMigrate(array $migrationIds)
    {
        $migrationIdsString = implode(',', $migrationIds);
        $this->printTaskInfo(
            'Execute migrations: <info>{migrationIdsString}</info>',
            ['migrationIdsString' => $migrationIdsString]
        );
        $this->drupal('migrate:execute '.static::escape($migrationIdsString));

        return $this;
    }

    /**
     * Executes `drupal list`.
     *
     * @return $this
     */
    public function listCommands()
    {
        return $this->drupal('list');
    }

    /**
     * Executes `drupal site:status`.
     *
     * @return $this
     */
    public function siteStatus()
    {
        return $this->drupal('site:status');
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
    public function drupal($command, $assumeYes = true)
    {
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
    protected function injectArguments($command, $assumeYes)
    {
        $optionsForNextCmd = '';
        foreach ($this->optionsForNextCmd as $option => $value) {
            $optionsForNextCmd .= ' --' . $option . (
                is_null($value) ? '' : '=' . static::escape($value)
            );
        }
        $cmd = $command;
        if (trim($this->arguments)) {
            $cmd .= $this->arguments;
        }
        if (trim($optionsForNextCmd)) {
            $cmd .= $optionsForNextCmd;
        }
        if ($assumeYes) {
            $cmd .= ' --yes';
        }
        $this->optionsForNextCmd = [];

        return $cmd;
    }
}
