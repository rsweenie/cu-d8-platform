<?php 

namespace Drupal\cu_hub_api\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
* @Constraint(
*   id = "UniqueHubPath",
*   label = @Translation("Unique hub path", context = "Validation"),
* )
*/

class UniqueHubPathConstraint extends Constraint {

  public $notUnique = 'The path alias %value is already in use on the site %site. Choose another path.';

}
