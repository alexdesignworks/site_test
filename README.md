# Site Test Drupal module
[![Circle CI](https://circleci.com/gh/alexdesignworks/site_test.svg?style=svg)](https://circleci.com/gh/alexdesignworks/site_test)

The Site Test module (https://www.drupal.org/project/site_test) is an extension of the Drupal core Simpletest module for running site specific tests in place on an active Drupal installation. 

The core `Simpletest` module creates a new Drupal installation with a blank database every time it runs a test, creating a large bottleneck and removing the ability to test with real-world data and configuration.

SiteTest allows for each test to run in 3 modes:

- Site - use current database tables.
- Clone - create copy of site tables.
- Core - create new tables for blank Drupal installation (core `Simpletest` module implementation).

## Example

```php
<?php
/**
 * @file
 * Site Test case example.
 */

/**
 * Class SiteTestExampleWebTestCase.
 */
class SiteTestExampleWebTestCase extends SiteWebTestCase {
  /**
   * Provide information about the site test.
   */
  public static function getInfo() {
    return [
      'name' => 'Example test',
      'description' => 'Description of the test.',
      'group' => 'Example group',
      'mode' => 'site',
    ];
  }

  /**
   * An example test.
   *
   * To create a new test, simply prefix a public function name in this class
   * with the word 'test' and it will be picked up by the system.
   * For example, testFoo and testBar functions would run as test cases.
   */
  public function testExample() {
    // Visit the user login page.
    $this->drupalGet('user/login');

    // Check the username field is present.
    $this->assertFieldByName('name', '', 'Username field found');
  }

  /**
   * Another example test.
   */
  public function testExampleTwo() {
    // Visit the admin page.
    $this->drupalGet('admin');

    // Ensure the user gets an access denied page.
    $this->assertResponse(403, 'Administration page was not accessible');
  }

}
```
