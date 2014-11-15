<?php

/**
 * @file
 * Common testing class for this Drupal site.
 */
abstract class SiteTestCase extends DrupalWebTestCase {
  /**
   * Test mode
   */
  protected $testMode;

  /**
   * Tables to exlude during data cloning, only their structure will be cloned.
   *
   * @var array
   */
  protected $excludeTables = array(
    'cache',
    'cache_block',
    'cache_bootstrap',
    'cache_field',
    'cache_filter',
    'cache_form',
    'cache_image',
    'cache_menu',
    'cache_page',
    'cache_path',
    'cache_update',
    'simpletest',
    'watchdog',
  );

  /**
   * Overrides default set up handler to prevent database sand-boxing.
   */
  protected function setUp() {
    switch ($this->getMethod()) {
      case 'core':
        $this->setUpForCore();
        break;

      case 'site':
        $this->setUpForOnSite();
        break;

      case 'clone':
        $this->setUpForClone();
        break;
    }
  }

  /**
   * Initialize a standard Drupal core simple test case.
   */
  protected function setUpForCore() {
    parent::setUp();
  }

  /**
   * Set up for on-site, non sandbox testing.
   */
  protected function setUpForOnSite() {
    // Use the test mail class instead of the default mail handler class.
    variable_set('mail_system', array('default-system' => 'TestingMailSystem'));
    $this->originalFileDirectory = variable_get('file_public_path', conf_path() . '/files');
    $this->public_files_directory = $this->originalFileDirectory;
    $this->private_files_directory = variable_get('file_private_path');
    $this->temp_files_directory = file_directory_temp();

    drupal_set_time_limit($this->timeLimit);
    $this->setup = TRUE;
  }

  /**
   * Set up for on-site, non sandbox testing.
   */
  protected function setUpForClone() {
    // Create the database prefix for this test.
    $this->prepareDatabasePrefix();

    // Prepare the environment for running tests.
    // This prepares directories and test_id.
    $this->prepareEnvironment();
    if (!$this->setupEnvironment) {
      return FALSE;
    }

    // Clone tables.
    $this->cloneTables();

    // Use the test mail class instead of the default mail handler class.
    variable_set('mail_system', array('default-system' => 'TestingMailSystem'));
    $this->originalFileDirectory = variable_get('file_public_path', conf_path() . '/files');
    $this->public_files_directory = $this->originalFileDirectory;
    $this->private_files_directory = variable_get('file_private_path');
    $this->temp_files_directory = file_directory_temp();

    drupal_set_time_limit($this->timeLimit);
    $this->setup = TRUE;
  }

  /**
   * Overrides default tear down handler to prevent database sandbox deletion.
   */
  protected function tearDown() {
    switch ($this->getMethod()) {
      case 'core':
        $this->tearDownForCore();
        break;

      case 'site':
        $this->tearDownForOnSite();
        break;

      case 'clone':
        $this->tearDownForClone();
        break;
    }
  }

  /**
   * Tear down for core based testing.
   */
  protected function tearDownForCore() {
    parent::tearDown();
  }

  /**
   * Tear down for on site testing.
   */
  protected function tearDownForOnSite() {
    // In case a fatal error occurred that was not in the test process read the
    // log to pick up any fatal errors.
    simpletest_log_read($this->testId, $this->databasePrefix, get_class($this), TRUE);

    $emailCount = count(variable_get('drupal_test_email_collector', array()));
    if ($emailCount) {
      $message = format_plural($emailCount, '1 e-mail was sent during this test.', '@count e-mails were sent during this test.');
      $this->pass($message, t('E-mail'));
    }

    // Close the CURL handler.
    $this->curlClose();
  }

  /**
   * Tear down for core based testing.
   */
  protected function tearDownForClone() {
    // In case a fatal error occurred that was not in the test process read the
    // log to pick up any fatal errors.
    simpletest_log_read($this->testId, $this->databasePrefix, get_class($this), TRUE);

    $emailCount = count(variable_get('drupal_test_email_collector', array()));
    if ($emailCount) {
      $message = format_plural($emailCount, '1 e-mail was sent during this test.', '@count e-mails were sent during this test.');
      $this->pass($message, t('E-mail'));
    }

    // Close the CURL handler.
    $this->curlClose();
  }

  /**
   * Get method for test.
   *
   * @return string
   *   The method of the test.
   */
  public function getMethod() {
    $info = $this->getInfo();
    $method = 'core';

    if (!empty($info['mode'])) {
      $method = $info['mode'];
    }

    return $method;
  }

  /**
   * Helper to clone existing DB tables.
   *
   * To clone existing tables into new ones, we need to get current and new
   * table names. Since any prefix-related information is stored with
   * connection, we have to switch database connection to test one, but not
   * earlier than data about current schema is gathered.
   */
  protected function cloneTables() {
    global $db_prefix;
    $db_prefix_current = $db_prefix;
    $db_prefix_test = $this->databasePrefix;

    // Retrieve schema for current installation.
    $this->databasePrefix = $db_prefix;
    $schemas = drupal_get_schema(NULL, TRUE);

    // Gather all prefixed source table names.
    $sources = array();
    foreach ($schemas as $name => $schema) {
      $sources[$name] = Database::getConnection()
        ->prefixTables('{' . $name . '}');
    }

    // Return test database prefix to original value.
    $this->databasePrefix = $db_prefix_test;
    $db_prefix = $db_prefix_test;

    // Change the database prefix to gather full destination table names.
    $this->changeDatabasePrefix();

    // Clone each table into the new test tables.
    foreach ($schemas as $name => $schema) {
      // Create new test table.
      // Current DB connection already have information about table prefixes.
      // Excluded tables needs to be created even if they have bo data.
      db_create_table($name, $schema);

      if (in_array($name, $this->excludeTables)) {
        continue;
      }

      $destination = Database::getConnection()->prefixTables('{' . $name . '}');
      db_query('INSERT INTO ' . $destination . ' SELECT * FROM ' . $sources[$name]);
    }

    $db_prefix = '';
  }
}
