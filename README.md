# Robo Drupal Console Extension

Extension to execute Drupal Console commands in [Robo](http://robo.li/).

Created based on [Robo DrushStack](https://github.com/boedah/robo-drush). Runs Drupal Console commands in stack. You can define global options for all commands (like Drupal root and uri).

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

Issue `composer require digipolisgent/robo-drupal-console:~0.1`

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
