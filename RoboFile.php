<?php

class RoboFile extends \Robo\Tasks
{

    use DigipolisGent\Robo\Task\DrupalConsole\loadTasks;

    public function test()
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit()
          ->option('disallow-test-output')
          ->option('report-useless-tests')
          ->option('strict-coverage')
          ->option('-v')
          ->option('-d error_reporting=-1')
          ->arg('tests')
          ->run();
    }

}
