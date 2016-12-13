<?php

namespace DigipolisGent\Robo\Task\DrupalConsole;

trait loadTasks {

  /**
   * Creates a DrupalConsoleStack task.
   *
   * @param string $pathToDrupalConsole
   *   The path to the Drupal Console executable.
   *
   * @return DrupalConsoleStack
   *   The Drupal console stack task.
   */
  protected function taskDrupalConsoleStack($pathToDrupalConsole = 'drupal') {
    return $this->task(DrupalConsoleStack::class, $pathToDrupalConsole);
  }

}
