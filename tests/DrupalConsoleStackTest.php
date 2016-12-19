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

    use \DigipolisGent\Robo\Task\DrupalConsole\loadTasks;
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
    protected function getTestCommand($method, array $args = [])
    {
        $stack = $this->taskDrupalConsoleStack();
        call_user_func_array([$stack, $method], $args);
        return $stack->getCommand();
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
    protected function getTestOptionCommand($method, $value = null)
    {
        $stack = $this->taskDrupalConsoleStack();
        call_user_func_array([$stack, $method], [$value]);
        $stack->drupal('command', false);
        return $stack->getCommand();
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
        $this->assertRegExp('#--root /var/www/html/app#', $command);
    }

    /**
     * Test the cache rebuild command.
     */
    public function testCacheRebuildCommand()
    {
        $this->assertEquals(
          'drupal cache:rebuild all') . ' --yes',
          $this->getTestCommand('cacheRebuild')
        );
        $this->assertEquals(
          'drupal cache:rebuild menu') . ' --yes',
          $this->getTestCommand('cacheRebuild', ['menu'])
        );
    }

    /**
     * Test the database update command.
     */
    public function testUpdateDbCommand()
    {
        $this->assertEquals(
          'drupal update:execute all --yes',
          $this->getTestCommand('updateDb', ['all'])
        );
        $this->assertEquals(
          'drupal update:execute system --yes',
          $this->getTestCommand('updateDb', ['system'])
        );
        $this->assertEquals(
          'drupal update:execute system 7001 --yes',
          $this->getTestCommand('updateDb', ['system', 7001])
        );
    }

    /**
     * Test the site maintenance mode command.
     */
    public function testMaintenanceCommand()
    {
        $this->assertEquals(
          'drupal site:maintenance on') . ' --yes',
          $this->getTestCommand('maintenance', [true])
        );
        $this->assertEquals(
          'drupal site:maintenance off') . ' --yes',
          $this->getTestCommand('maintenance', [false])
        );
    }

    /**
     * Test the cron command.
     */
    public function testExecuteCronCommand()
    {
        $this->assertEquals(
          'drupal cron:execute system') . ' --yes',
          $this->getTestCommand('executeCron', ['system'])
        );
    }

    /**
     * Test the site install command.
     */
    public function testSiteInstallCommand()
    {
        $this->assertEquals(
          'drupal site:install --yes',
          $this->getTestCommand('siteInstall')
        );
    }

    /**
     * Test the config export command.
     */
    public function testConfigExportCommand()
    {
        $this->assertEquals(
          'drupal config:export --yes',
          $this->getTestCommand('configExport')
        );
    }

    /**
     * Test the config import command.
     */
    public function testConfigImportCommand()
    {
        $this->assertEquals(
          'drupal config:import --yes',
          $this->getTestCommand('configImport')
        );
    }

    /**
     * Test the database dump command.
     */
    public function testDbDumpCommand()
    {
        $this->assertEquals(
          'drupal database:dump mydb') . ' --yes',
          $this->getTestCommand('dbDump', ['mydb'])
        );
    }

    /**
     * Test the database restore command.
     */
    public function testDbRestoreCommand()
    {
        $this->assertEquals(
          'drupal database:restore mydb') . ' --yes',
          $this->getTestCommand('dbRestore', ['mydb'])
        );
    }

    /**
     * Test the database drop command.
     */
    public function testDbDropCommand()
    {
        $this->assertEquals(
          'drupal database:drop mydb') . ' --yes',
          $this->getTestCommand('dbDrop', ['mydb'])
        );
    }

    /**
     * Test the migrate command.
     */
    public function testDbExecuteMigrateCommand()
    {
        $ids = [1,2,3];
        $this->assertEquals(
          'drupal migrate:execute ' . implode(',', $ids) . ' --yes',
          $this->getTestCommand('executeMigrate', [$ids])
        );
    }

    /**
     * Test the list command.
     */
    public function testListCommand()
    {
        $this->assertEquals(
          'drupal list --yes',
          $this->getTestCommand('listCommands')
        );
        $this->getTestCommand('listCommands', ['list']);
    }

    /**
     * Test the site status command.
     */
    public function testSiteStatusCommand()
    {
        $this->assertEquals(
          'drupal site:status --yes',
          $this->getTestCommand('siteStatus')
        );
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
        $this->assertEquals(
          'drupal command --root ' . static::escape(__DIR__),
          $this->getTestOptionCommand('drupalRootDirectory', __DIR__)
        );
    }

    /**
     * Test the uri option.
     */
    public function testUriOption() {
        $this->assertEquals(
          'drupal command --uri http://example.com'),
          $this->getTestOptionCommand('uri', 'http://example.com')
        );
    }

    /**
     * Test the environment option.
     */
    public function testEnvironmentOption() {
        $this->assertEquals(
          'drupal command --env prod'),
          $this->getTestOptionCommand('environment', 'prod')
        );
    }

    /**
     * Test the no debug option.
     */
    public function testNoDebugOption() {
        $this->assertEquals(
          'drupal command --no-debug',
          $this->getTestOptionCommand('noDebug')
        );
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
            $this->assertEquals(
              'drupal command --verbose ' . $level,
              $this->getTestOptionCommand('verbose', $level)
            );
        }
    }

    /**
     * Test the site name option.
     */
    public function testSiteNameOption() {
        $this->assertEquals(
          'drupal command --site-name Digipolis'),
          $this->getTestOptionCommand('siteName', 'Digipolis')
        );
    }

    /**
     * Test the site mail option.
     */
    public function testSiteMailOption() {
        $this->assertEquals(
          'drupal command --site-mail digipolis@example.com'),
          $this->getTestOptionCommand('siteMail', 'digipolis@example.com')
        );
    }

    /**
     * Test the file option.
     */
    public function testFileOption() {
        $this->assertEquals(
          'drupal command --file ' . static::escape(__FILE__),
          $this->getTestOptionCommand('file', __FILE__)
        );
    }

    /**
     * Test the directory option.
     */
    public function testDirectoryOption() {
        $this->assertEquals(
          'drupal command --directory ' . static::escape(__DIR__),
          $this->getTestOptionCommand('directory', __DIR__)
        );
    }

    /**
     * Test the tar option.
     */
    public function testTarOption() {
        $this->assertEquals(
          'drupal command --tar',
          $this->getTestOptionCommand('tar')
        );
    }

    /**
     * Test the langcode option.
     */
    public function testLangcodeOption() {
        $this->assertEquals(
          'drupal command --langcode nl'),
          $this->getTestOptionCommand('langcode', 'nl')
        );
    }

    /**
     * Test the db type option.
     */
    public function testDbTypeOption() {
        $this->assertEquals(
          'drupal command --db-type mysql'),
          $this->getTestOptionCommand('dbType', 'mysql')
        );
    }

    /**
     * Test the db file option.
     */
    public function testDbFileOption() {
        $this->assertEquals(
          'drupal command --db-file .h.sqlite'),
          $this->getTestOptionCommand('dbFile', '.h.sqlite')
        );
    }

    /**
     * Test the db host option.
     */
    public function testDbHostOption() {
        $this->assertEquals(
          'drupal command --db-host localhost'),
          $this->getTestOptionCommand('dbHost', 'localhost')
        );
    }

    /**
     * Test the db name option.
     */
    public function testDbNameOption() {
        $this->assertEquals(
          'drupal command --db-name db_digipolis'),
          $this->getTestOptionCommand('dbName', 'db_digipolis')
        );
    }

    /**
     * Test the db user option.
     */
    public function testDbUserOption() {
        $this->assertEquals(
          'drupal command --db-user db_user'),
          $this->getTestOptionCommand('dbUser', 'db_user')
        );
    }

    /**
     * Test the db password option.
     */
    public function testDbPassOption() {
        $this->assertEquals(
          'drupal command --db-pass db_pass'),
          $this->getTestOptionCommand('dbPass', 'db_pass')
        );
    }

    /**
     * Test the db prefix option.
     */
    public function testDbPrefixOption() {
        $this->assertEquals(
          'drupal command --db-prefix prefix_'),
          $this->getTestOptionCommand('dbPrefix', 'prefix_')
        );
    }

    /**
     * Test the db port option.
     */
    public function testDbPortOption() {
        $this->assertEquals(
          'drupal command --db-port 1234'),
          $this->getTestOptionCommand('dbPort', '1234')
        );
    }

    /**
     * Test the account mail option.
     */
    public function testAccountMailOption() {
        $this->assertEquals(
          'drupal command --account-mail account@example.com'),
          $this->getTestOptionCommand('accountMail', 'account@example.com')
        );
        $this->getTestOptionCommand('accountMail', 'account-mail', 'account@example.com');
    }

    /**
     * Test the account name option.
     */
    public function testAccountNameOption() {
        $this->assertEquals(
          'drupal command --account-name accountName'),
          $this->getTestOptionCommand('accountName', 'accountName')
        );
        $this->getTestOptionCommand('accountName', 'account-name', 'accountName');
    }

    /**
     * Test the account password option.
     */
    public function testAccountPassOption() {
        $this->assertEquals(
          'drupal command --account-pass MyPwD123_'),
          $this->getTestOptionCommand('accountPass', 'MyPwD123_')
        );
        $this->getTestOptionCommand('accountPass', 'account-pass', 'MyPwD123_');
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
