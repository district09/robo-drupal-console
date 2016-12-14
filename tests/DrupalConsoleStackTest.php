<?php
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
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()
          ->get('collectionBuilder', [$emptyRobofile]);
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
        $this->assertEquals(2,
          preg_match_all('#--root /var/www/html/app#', $command));
    }

    /**
     * Test the config export command.
     */
    public function testConfigExportCommand()
    {
        $command = $this->taskDrupalConsoleStack()
          ->directory('sites/default/config')
          ->tar()
          ->configExport()
          ->getCommand();
        $expected = 'drupal config:export --yes'
          . ' --tar --directory=' . static::escape('sites/default/config');
        $this->assertEquals($expected, $command);
    }

    /**
     * Test the config import command.
     */
    public function testConfigImportCommand()
    {
        $command = $this->taskDrupalConsoleStack()
          ->directory('sites/default/config')
          ->configImport()
          ->getCommand();
        $expected = 'drupal config:import --yes'
          . ' --directory=' . static::escape('sites/default/config');
        $this->assertEquals($expected, $command);
    }

    /**
     * Test the site install command with an SQLite database.
     */
    public function testSiteInstallSqliteCommand()
    {
        $command = $this->taskDrupalConsoleStack()
          ->siteName('Site Name')
          ->siteMail('site-mail@example.com')
          ->langcode('de')
          ->accountMail('mail@example.com')
          ->accountName('admin')
          ->accountPass('pw')
          ->dbPrefix('drupal_')
          ->dbType('sqlite')
          ->dbFile('sites/default/.ht.sqlite')
          ->siteInstall('minimal')
          ->getCommand();
        $expected = 'drupal site:install minimal --yes'
          . ' --db-file='. static::escape('sites/default/.ht.sqlite')
          . ' --db-type=' . static::escape('sqlite')
          . ' --db-prefix=' . static::escape('drupal_')
          . ' --account-pass=' . static::escape('pw')
          . ' --account-name=' . static::escape('admin')
          . ' --account-mail=' . static::escape('mail@example.com')
          . ' --langcode=' . static::escape('de')
          . ' --site-mail='. static::escape('site-mail@example.com')
          . ' --site-name=' . static::escape('Site Name');
        $this->assertEquals($expected, $command);
    }

    /**
     * Test the site install command with a MySQL database
     */
    public function testSiteInstallMysqlCommand()
    {
        $command = $this->taskDrupalConsoleStack()
          ->siteName('Site Name Mysql')
          ->siteMail('site-mail2@example.com')
          ->langcode('fr')
          ->accountMail('mail2@example.com')
          ->accountName('admin-user')
          ->accountPass('passw')
          ->dbPrefix('drupal_')
          ->dbType('mysql')
          ->dbHost('localhost')
          ->dbName('testdb')
          ->dbUser('dbuser')
          ->dbPass('testdbpw')
          ->siteInstall('standard')
          ->getCommand();
        $expected = 'drupal site:install standard --yes'
          . ' --db-pass=' . static::escape('testdbpw')
          . ' --db-user=' . static::escape('dbuser')
          . ' --db-name=' . static::escape('testdb')
          . ' --db-host=' . static::escape('localhost')
          . ' --db-type=' . static::escape('mysql')
          . ' --db-prefix=' . static::escape('drupal_')
          . ' --account-pass=' . static::escape('passw')
          . ' --account-name=' . static::escape('admin-user')
          . ' --account-mail=' . static::escape('mail2@example.com')
          . ' --langcode=' . static::escape('fr')
          . ' --site-mail=' . static::escape('site-mail2@example.com')
          . ' --site-name=' . static::escape('Site Name Mysql');
        $this->assertEquals($expected, $command);
    }

    /**
     * Test the Drupal Console list command.
     */
    public function testDrupalConsoleList()
    {
        $result = $this->taskDrupalConsoleStack(__DIR__ . '/../vendor/bin/drupal')
          ->printed(false)
          ->listCommands()
          ->run();
        $this->assertEquals(trim($result->getMessage()),
          'DrupalConsole must be executed within a Drupal Site.');
        $this->assertFalse($result->wasSuccessful(),
          'Exit code was: ' . $result->getExitCode());
    }

}
