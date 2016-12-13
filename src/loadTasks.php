<?php

namespace DigipolisGent\Robo\Task\Drush;

trait loadTasks
{
  /**
   * @param string $pathToDrupalConsole
   * @return DrupalConsoleStack
   */
  protected function taskDrupalConsoleStack($pathToDrupalConsole = 'drupal')
  {
    return $this->task(DrupalConsoleStack::class, $pathToDrupalConsole);
  }
}