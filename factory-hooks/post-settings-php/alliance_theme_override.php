<?php

/*
* A temporary solution to theme default settings not sticking on update
*/
$alliance_hosts = ['alliance.creighton.edu', 'www.alliance.creighton.edu', 'alliance.creighton.acsitefactory.com', 'alliance.test-creighton.acsitefactory.com', 'alliance.dev-creighton.acsitefactory.com'];
$host = $_SERVER['SERVER_NAME'];
if (in_array($host, $alliance_hosts)) {
  $config['system.theme']['default'] = 'cu2017_alliance';
}