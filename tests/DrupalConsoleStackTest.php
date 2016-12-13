<?php

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class DrupalConsoleStackTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface {

  use DigipolisGent\Robo\Task\DrupalConsole\loadTasks;
  use TaskAccessor;
  use ContainerAwareTrait;

  // Set up the Robo container so that we can create tasks in our tests.
  public function setup() {
    $container = Robo::createDefaultContainer(null, new NullOutput());
    $this->setContainer($container);
  }

  // Scaffold the collection builder
  public function collectionBuilder() {
    $emptyRobofile = new \Robo\Tasks;
    return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
  }

  public function testYesIsAssumed() {
    $command = $this->taskDrupalConsoleStack()
      ->exec('command')
      ->getCommand();
    $this->assertEquals('drupal command --yes', $command);
  }

  public function testAbsenceofYes() {
    $command = $this->taskDrupalConsoleStack()
      ->exec('command', false)
      ->getCommand();
    $this->assertEquals('drupal command', $command);
  }

  public function testOptionsArePrependedBeforeEachCommand() {
    $command = $this->taskDrupalConsoleStack()
      ->drupalRootDirectory('/var/www/html/app')
      ->exec('command-1')
      ->exec('command-2')
      ->getCommand();
    $this->assertEquals(2, preg_match_all('#-r /var/www/html/app#', $command));
  }

  public function testSiteInstallCommand() {
    $command = $this->taskDrupalConsoleStack()
      ->siteName('Site Name')
      ->siteMail('site-mail@example.com')
      ->locale('de')
      ->accountMail('mail@example.com')
      ->accountName('admin')
      ->accountPass('pw')
      ->dbPrefix('drupal_')
      ->dbType('sqlite')
      ->dbFile('sites/default/.ht.sqlite')
      ->disableUpdateStatusModule()
      ->siteInstall('minimal')
      ->getCommand();
    $expected = 'drupal site:install minimal --yes --site-name=' . escapeshellarg('Site Name')
      . ' --site-mail=site-mail@example.com'
      . ' --langcode=de --account-mail=mail@example.com --account-name=' . escapeshellarg('admin')
      . ' --account-pass=pw'
      . ' --db-prefix=drupal_ --db-type=mysql --db-type=mysql' . escapeshellarg('sites/default/.ht.sqlite');
    $this->assertEquals($expected, $command);
  }

  public function testDrupalConsoleStatus() {
    $result = $this->taskDrupalConsoleStack(__DIR__ . '/../vendor/bin/drupal')
      ->printed(false)
      ->siteStatus()
      ->run();
    $this->assertTrue($result->wasSuccessful(), 'Exit code was: ' . $result->getExitCode());
  }

}
