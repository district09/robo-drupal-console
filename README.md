# Robo Drupal Console Extension

Extension to execute Drupal Console commands in [Robo](http://robo.li/).

[![Latest Stable Version](https://poser.pugx.org/digipolisgent/robo-drupal-console/v/stable)](https://packagist.org/packages/digipolisgent/robo-drupal-console)
[![Latest Unstable Version](https://poser.pugx.org/digipolisgent/robo-drupal-console/v/unstable)](https://packagist.org/packages/digipolisgent/robo-drupal-console)
[![Total Downloads](https://poser.pugx.org/digipolisgent/robo-drupal-console/downloads)](https://packagist.org/packages/digipolisgent/robo-drupal-console)
[![License](https://poser.pugx.org/digipolisgent/robo-drupal-console/license)](https://packagist.org/packages/digipolisgent/robo-drupal-console)

[![Build Status](https://travis-ci.org/digipolisgent/robo-drupal-console.svg?branch=develop)](https://travis-ci.org/digipolisgent/robo-drupal-console)
[![Maintainability](https://api.codeclimate.com/v1/badges/48b91548eb03ababa9f8/maintainability)](https://codeclimate.com/github/digipolisgent/robo-drupal-console/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/48b91548eb03ababa9f8/test_coverage)](https://codeclimate.com/github/digipolisgent/robo-drupal-console/test_coverage)
[![PHP 7 ready](https://php7ready.timesplinter.ch/digipolisgent/robo-drupal-console/develop/badge.svg)](https://travis-ci.org/digipolisgent/robo-drupal-console)

Created based on [Robo DrushStack](https://github.com/boedah/robo-drush). Runs
Drupal Console commands in stack. You can define global options for all
commands (like Drupal root and uri).

## Table of contents

- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [Examples](#examples)

## Installation

Add `"digipolisgent/robo-drupal-console": "~0.1"` to your composer.json:

```json
    {
        "require-dev": {
            "digipolisgent/robo-drupal-console": "~0.1"
        }
    }
```

and execute `composer update`.

OR

Issue `composer require --dev digipolisgent/robo-drupal-console:~0.1`

## Usage

Use the trait (according to your used version) in your RoboFile:

```php
class RoboFile extends \Robo\Tasks
{
    use DigipolisGent\Robo\Task\DrupalConsole\loadTasks;
}
```

## Testing

`composer test`

## Examples

### Site update

This executes pending database updates and reverts all features (from code to database):

### Site install
