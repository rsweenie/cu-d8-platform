# Creighton Notes

## This directory

This directory is owned by the Acquia lightning distribution. This is 
not a default Drupal location. 

To understand how Drupal settings files are included, see:

  `vendor/acquia/blt/settings/blt.settings.php`

To understand how BLT determines what environmental vars are so it can include
particular setting files, see: 

  `vendor/acquia/blt/src/Robo/Common/EnvironmentDetector.php`
  
These two files are the best way to cut through the Acquia FUD as it relates 
to Drupal settings.php configuration.

## Vagrant and Lando

We are using includes.settings.php to add highly customized settings files for our
dev environments. Currently, the only case here is for Lando. Lando requires a special
set of database credentials and that's about it.

We do nothing in a Vagrant environment, primarily because there is no need (devs 
[or BLT] simply configure there local.settings.php file in this same dir), and also
because you can't easily detect if running within Vagrant because it's a full on VM
and doesn't set a lot of easy to detect ENV vars like Lando does.