CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Writing your first test
 * Maintainers

INTRODUCTION
------------
The Site Test module is an extension of the Drupal core Simpletest module for
running site specific tests in place on an active Drupal installation. The core
Simpletest module creates a new Drupal installation with a blank database every
time it runs a test creating a large bottleneck and removing the ability to test
with real-world data and configuration.

REQUIREMENTS
------------
This module requires the following modules:
 * Simpletest (core)

WRITING YOUR FIRST TEST
-----------------------
 1. Enable the site_test module on your site.
 2. In a custom module or feature, create a new folder called 'tests'.
 3. Copy the file 'site_test.example.test' from the site_test/examples folder
    into your modules test folder and rename to suit the objective of your test.
 4. Change the getInfo values to reflect the nature of your test.
 5. In your modules .info file add the test file using:
    files[] = tests/site_test.example.test
 6. Clear cache and visit admin/config/development/testing/site_test to run your
    test.

MAINTAINERS
-----------
Current maintainers:
 * Marton Bodonyi (interactivejunky) - https://www.drupal.org/user/1633774
 * Alex Skrypnyk (alexdesignworks) - https://www.drupal.org/user/620694
