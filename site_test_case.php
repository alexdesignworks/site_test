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
}
