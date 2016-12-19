<?php

class RoboFile extends \Robo\Tasks
{

    use DigipolisGent\Robo\Task\DrupalConsole\loadTasks;

    /**
     * Runs the unit tests.
     */
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

    /**
     * Provides test coverage for Scrutinizer
     */
    public function testCoverageScrutinize()
    {
        $this->stopOnFail(true);
        $this->taskPHPUnit()
            ->option('disallow-test-output')
            ->option('report-useless-tests')
            ->option('strict-coverage')
            ->option('-d error_reporting=-1')
            ->option('--coverage-clover=coverage.clover')
            ->arg('tests')
            ->run();
    }

}
