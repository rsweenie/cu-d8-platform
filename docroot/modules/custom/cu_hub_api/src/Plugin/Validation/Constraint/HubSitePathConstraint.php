<?php 

namespace Drupal\cu_hub_api\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
* @Constraint(
*   id = "HubSitePath",
*   label = @Translation("Unique hub site path", context = "Validation"),
* )
*/

class HubSitePathConstraint extends Constraint {

  public $notUnique = 'The path alias %alias is already in use on %site. Choose another path.';

}
