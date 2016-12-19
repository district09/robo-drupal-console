<?php
namespace DigipolisGent\Tests\Robo\Task\DrupalConsole;

use DigipolisGent\Robo\Task\DrupalConsole\DrupalConsoleStack;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\CommandArguments;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class DrupalConsoleStackTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface
{

    use DigipolisGent\Robo\Task\DrupalConsole\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;
    use CommandArguments;

    /**
     * Set up the Robo container so that we can create tasks in our tests.
     */
    public function setUp()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
    }

    /**
     * Scaffold the collection builder.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The collection builder.
     */
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        return $this->getContainer()
            ->get('collectionBuilder', [$emptyRobofile]);
    }

    /**
     * Tests a given command.
     *
     * @param string $method
     *   The method to call on DrupalConsoleStack.
     * @param string $command
     *   The expected command, without the 'drupal ' prefix and ' --yes' suffix.
     * @param array $args
     */
    protected function doTestCommand($method, $command, array $args = [])
    {
        $stack = $this->taskDrupalConsoleStack();
        call_user_func_array([$stack, $method], $args);
        $expected = 'drupal ' . $command . ' --yes';
        $this->assertEquals($expected, $stack->getCommand());
    }

    /**
     * Test a given option.
     *
     * @param string $method
     *   The method to call on DrupalConsoleStack.
     * @param string $option
     *   The option name.
     * @param string $value
     *   The option value.
     */
    protected function doTestOption($method, $option, $value = null)
    {
        $stack = $this->taskDrupalConsoleStack();
        call_user_func_array([$stack, $method], [$value]);
        $expected = 'drupal command --' . $option;
        if (!is_null($value)) {
            $expected .= ' ' . static::escape($value);
        }
        $stack->drupal('command', false);
        $this->assertEquals($expected, $stack->getCommand());
    }

    /**
     * Test if the yes option is assumed by default.
     */
    public function testYesIsAssumed()
    {
        $command = $this->taskDrupalConsoleStack()
          ->drupal('command')
          ->getCommand();
        $this->assertEquals('drupal command --yes', $command);
    }

    /**
     * Test the absence of the yes option if explicitly declared.
     */
    public function testAbsenceofYes()
    {
        $command = $this->taskDrupalConsoleStack()
          ->drupal('command', false)
          ->getCommand();
        $this->assertEquals('drupal command', $command);
    }

    /**
     * Test if options are prepended before each command.
     */
    public function testOptionsArePrependedBeforeEachCommand()
    {
        $command = $this->taskDrupalConsoleStack()
          ->drupalRootDirectory('/var/www/html/app')
          ->drupal('command-1')
          ->drupal('command-2')
          ->getCommand();
        $this->assertEquals(2, preg_match_all('#--root /var/www/html/app#', $command));
    }

    /**
     * Test the cache rebuild command.
     */
    function testCacheRebuildCommand()
    {
        $this->doTestCommand('cacheRebuild', 'cache:rebuild ' . static::escape('all'));
        $this->doTestCommand('cacheRebuild', 'cache:rebuild ' . static::escape('menu'), ['menu']);
    }

    /**
     * Test the database update command.
     */
    public function testUpdateDbCommand()
    {
        $this->doTestCommand('updateDb', 'update:execute ' . static::escape('all'));
        $this->doTestCommand('updateDb', 'update:execute ' . static::escape('system'), ['system']);
        $this->doTestCommand('updateDb', 'update:execute ' . static::escape('system') . ' ' . static::escape(7001), ['system', 7001]);
    }

    /**
     * Test the site maintenance mode command.
     */
    public function testMaintenanceCommand()
    {
        $this->doTestCommand('maintenance', 'site:maintenance ' . static::escape('on'), [true]);
        $this->doTestCommand('maintenance', 'site:maintenance ' . static::escape('off'), [false]);
    }

    /**
     * Test the cron command.
     */
    public function testExecuteCronCommand()
    {
        $this->doTestCommand('executeCron', 'cron:execute ' . static::escape('system'), ['system']);
    }

    /**
     * Test the site install command.
     */
    function testSiteInstallCommand()
    {
        $this->doTestCommand('siteInstall', 'site:install');
    }

    /**
     * Test the config export command.
     */
    public function testConfigExportCommand()
    {
        $this->doTestCommand('configExport', 'config:export');
    }

    /**
     * Test the config import command.
     */
    public function testConfigImportCommand()
    {
        $this->doTestCommand('configImport', 'config:import');
    }

    /**
     * Test the database dump command.
     */
    public function testDbDumpCommand()
    {
        $this->doTestCommand('dbDump', 'database:dump ' . static::escape('mydb'), ['mydb']);
    }

    /**
     * Test the database restore command.
     */
    public function testDbRestoreCommand()
    {
        $this->doTestCommand('dbRestore', 'database:restore ' . static::escape('mydb'), ['mydb']);
    }

    /**
     * Test the database drop command.
     */
    public function testDbDropCommand()
    {
        $this->doTestCommand('dbDrop', 'database:drop ' . static::escape('mydb'), ['mydb']);
    }

    /**
     * Test the migrate command.
     */
    public function testDbExecuteMigrateCommand()
    {
        $ids = [1,2,3];
        $this->doTestCommand('executeMigrate', 'migrate:execute ' . static::escape(implode(',', $ids)), [$ids]);
    }

    /**
     * Test the list command.
     */
    function testListCommand()
    {
        $this->doTestCommand('listCommands', 'list');
    }

    /**
     * Test the site status command.
     */
    function testSiteStatusCommand()
    {
        $this->doTestCommand('siteStatus', 'site:status');
    }

    /**
     * Test the Drupal Console list command outside of a Drupal site.
     */
    public function testListCommandOutsideDrupalSite()
    {
        $result = $this->taskDrupalConsoleStack(__DIR__ . '/../vendor/bin/drupal')
          ->printed(false)
          ->listCommands()
          ->run();
        $this->assertEquals(trim($result->getMessage()), 'DrupalConsole must be executed within a Drupal Site.');
        $this->assertFalse($result->wasSuccessful(), 'Exit code was: ' . $result->getExitCode());
    }

    /**
     * Test the root directory option.
     */
    public function testDrupalRootDirectoryOption() {
        $this->doTestOption('drupalRootDirectory', 'root', __DIR__);
    }

    /**
     * Test the uri option.
     */
    public function testUriOption() {
        $this->doTestOption('uri', 'uri', 'http://example.com');
    }

    /**
     * Test the environment option.
     */
    public function testEnvironmentOption() {
        $this->doTestOption('environment', 'env', 'prod');
    }

    /**
     * Test the no debug option.
     */
    public function testNoDebugOption() {
        $this->doTestOption('noDebug', 'no-debug');
    }

    /**
     * Test the verbose option.
     */
    public function testVerboseOption() {
        $levels = [
            DrupalConsoleStack::VERBOSITY_LEVEL_NORMAL,
            DrupalConsoleStack::VERBOSITY_LEVEL_VERBOSE,
            DrupalConsoleStack::VERBOSITY_LEVEL_DEBUG,
        ];
        foreach ($levels as $level) {
            $this->doTestOption('verbose', 'verbose', $level);
        }
    }

    /**
     * Test the site name option.
     */
    public function testSiteNameOption() {
        $this->doTestOption('siteName', 'site-name', 'Digipolis');
    }

    /**
     * Test the site mail option.
     */
    public function testSiteMailOption() {
        $this->doTestOption('siteMail', 'site-mail', 'digipolis@example.com');
    }

    /**
     * Test the file option.
     */
    public function testFileOption() {
        $this->doTestOption('file', 'file', __FILE__);
    }

    /**
     * Test the directory option.
     */
    public function testDirectoryOption() {
        $this->doTestOption('directory', 'directory', __DIR__);
    }

    /**
     * Test the tar option.
     */
    public function testTarOption() {
        $this->doTestOption('tar', 'tar');
    }

    /**
     * Test the langcode option.
     */
    public function testLangcodeOption() {
        $this->doTestOption('langcode', 'langcode', 'nl');
    }

    /**
     * Test the db type option.
     */
    public function testDbTypeOption() {
        $this->doTestOption('dbType', 'db-type', 'mysql');
    }

    /**
     * Test the db file option.
     */
    public function testDbFileOption() {
        $this->doTestOption('dbFile', 'db-file', '.h.sqlite');
    }

    /**
     * Test the db host option.
     */
    public function testDbHostOption() {
        $this->doTestOption('dbHost', 'db-host', 'localhost');
    }

    /**
     * Test the db name option.
     */
    public function testDbNameOption() {
        $this->doTestOption('dbName', 'db-name', 'db_digipolis');
    }

    /**
     * Test the db user option.
     */
    public function testDbUserOption() {
        $this->doTestOption('dbUser', 'db-user', 'db_user');
    }

    /**
     * Test the db password option.
     */
    public function testDbPassOption() {
        $this->doTestOption('dbPass', 'db-pass', 'db_pass');
    }

    /**
     * Test the db prefix option.
     */
    public function testDbPrefixOption() {
        $this->doTestOption('dbPrefix', 'db-prefix', 'prefix_');
    }

    /**
     * Test the db port option.
     */
    public function testDbPortOption() {
        $this->doTestOption('dbPort', 'db-port', '1234');
    }

    /**
     * Test the account mail option.
     */
    public function testAccountMailOption() {
        $this->doTestOption('accountMail', 'account-mail', 'account@example.com');
    }

    /**
     * Test the account name option.
     */
    public function testAccountNameOption() {
        $this->doTestOption('accountName', 'account-name', 'accountName');
    }

    /**
     * Test the account password option.
     */
    public function testAccountPassOption() {
        $this->doTestOption('accountPass', 'account-pass', 'MyPwD123_');
    }

    /**
     * Test the getVersion method.
     */
    public function testGetVersion() {
        // @todo: fails: DrupalConsole must be executed within a Drupal Site.
        //$this->assertEquals(Drupal\Console\Application::VERSION, $this->taskDrupalConsoleStack(__DIR__ . '/../vendor/bin/drupal')->getVersion());
        $this->assertEquals('unknown', $this->taskDrupalConsoleStack(__DIR__ . '/../vendor/bin/drupal')->getVersion());
    }
}
