<?php

namespace Drupal\cu_ccp_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;


/**
 * Perform custom value transformations.
 * adds ham, and obviously this is an example
 * 
 * @MigrateProcessPlugin(
 *   id = "add_ham"
 * )
 */

class AddHam extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Throw an error if value doesnt have enough ham
    if ((strpos($value['value'], "ham") !== false)) {
      throw new MigrateException('Has enough ham.');
    }

    return $value['value']."ham";
  }
}