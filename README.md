# Drupal 8 Creighton University

This project encompasses the Drupal 8 multisite platform for Creighton University marketing sites.

* [UCOM focused Documentation](https://cu-webteam.github.io/d8-platform/)
* [Dev Team Documentation](https://github.com/cu-webteam/d8-platform/wiki)

[![Travis (.com)](https://img.shields.io/travis/cu-webteam/d8-platform.svg)](https://travis-ci.com/cu-webteam/d8-platform)

## Table of Contents

* [Getting Started - Mac](#getting-started-mac)
* [Get Code](#get-code)
* [Local Environment Setup](#local-environment-setup)
* [Virtual Machine](#virtual-machine)
* [Working With BLT](#working-with-blt)
* [Resources](#resources)

## Getting Started (Mac)

This project is based on BLT, an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia Professional Services best practices. While this is one of many methodologies, it is our recommended methodology.

* Review the [Required / Recommended Skills](http://blt.readthedocs.io/en/latest/readme/skills) for working with a BLT project
* Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [System Requirements](http://blt.readthedocs.io/en/latest/INSTALL/#system-requirements) for more information.
  * If you don't have XCode installed:
    * `sudo xcodebuild -license`
    * `xcode-select --install`
  * If you don't have Homebrew installed:
    * `/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"`
  * PHP 7.1, Composer, and Git are required. Use Homebrew to install whichever dependencies are missing from your computer:
    * `brew install php71 mcrypt git composer`
    * `composer global require "hirak/prestissimo:^0.3"`
  * In order to run the Drupal VM, Virtualbox and Vagrant are required:
    * `brew tap caskroom/cask`
    * `brew cask install virtualbox vagrant`
    * `vagrant plugin install vagrant-hostsupdater`
* Request access to the cu-webteam github organization 
* Request access to the Acquia Cloud Environment for your project 
* Set up an SSH key that can be used for Github and Acquia Cloud (you CAN use the same key)
  * [Set up Acquia Cloud SSH Keys](https://docs.acquia.com/acquia-cloud/ssh/generate)
  * [Set up Github SSH Keys](https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/)

## Get Code

```shell
~$ git clone git@github.com:cu-webteam/d8-platform.git <project_root>
```

## Local Environment Setup

BLT requires a local environment that implements a LAMP stack. While out of the box templates are provided for Drupal VM, if you prefer you can use another tool such as Docker, Docksal, Lando, (other) Vagrant, or your own custom LAMP stack. BLT works with any local environment, however support is limited for these solutions.

* Install Composer Dependencies (warning: this can take some time based on internet speeds)

```shell
project_root$ composer install
```

## Virtual Machine

* Check VirtualBox to ensure that you do not already have a VM named local.creighton.com - if you do, delete it. When prompted, select 'remove all files'.
* Check your hosts file to ensure that you do not already have entries for local.creighton.com - if you do, delete them.

* Create a file in the blt directory named `local.blt.yml`

```shell
project_root$ touch blt/local.blt.yml
```

* Build the vm using BLT

```shell
project_root$ vendor/bin/blt vm
```

  a) Drupal VM is not currently installed. Install it now? (y/n) `y`

  b) Which base box would you like to use? `0`

  c) Do you want to boot Drupal VM? (y/n) `y`

  d) creighton: Pruning invalid NFS exports. Administrator privileges will be required...

```shell
project_root$ vagrant ssh
vagrant@local:/var/www/creighton$ blt setup
```

  a) You are about to DROP all tables in your 'drupal' database. Do you want to continue? (yes/no) [yes]: `yes`

* Access the site and do necessary work at local.creighton.com by running

```shell
vagrant@local:/var/www/creighton$ drush uli
```

BLT 9 and Drush 9 require all blt and drush commands to be executed inside of the VM. Because of this requirement, the VM must have SSH access to Acquia.

```shell
vagrant@local:/var/www/creighton$ cd ~/.ssh
vagrant@local:/var/www/creighton$ ssh-keygen -b 4096
```

* The public key needs to be added to your Acquia Cloud account. More detail can be found about that [here](https://docs.acquia.com/acquia-cloud/ssh/generate).

* Install Drush Launcher, using the instructions in [this](https://github.com/drush-ops/drush-launcher) Github repository.

## Working With BLT

Additional [BLT documentation](http://blt.readthedocs.io) may be useful. You may also access a list of BLT commands by running

```shell
project_url$ vendor/bin/blt
```

Note the following properties of this project:

* Primary development branch: master - deploys to dev environment in ACSF
  * feature branching - branching per feature
* Local environment: local
* Local site URL: local.creighton.com

BLT projects are designed to instill software development best practices (including git workflows).

Our BLT Developer documentation includes an [example workflow](http://blt.readthedocs.io/en/latest/readme/dev-workflow/#workflow-example-local-development).

### Important Configuration Files

BLT uses a number of configuration (.yml or .json) files to define and customize behaviors. Some examples of these are:

* `blt/blt.yml` (formerly `blt/project.yml` prior to BLT 9.x)
* `blt/local.blt.yml`
* `box/config.yml` - Drupal VM configuration
* `drush/sites` - contains Drush aliases for this project
* `composer.json` - includes required components, including Drupal Modules, for this project

## XDebug and VSCode

[VSCode XDebug Extension provided by Felix Becker](https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug) allows local XDebug capabilities. To leverage remote debugging there are a few steps involved:

* `<project root>/box/config.yml` settings are set automatically to configure XDebug in your Vagrant box after running ```vendor/bin/blt vm```
* Install PHP Debug VSCode Extension by Felix Becker
* Also see https://www.drupal.org/docs/develop/development-tools/configuring-visual-studio-code under section Configuring XDebug
* See `<project root>/vscode-launch.json.md` file for an example of additional pathMappings in your VSCode configuration in `.vscode/launch.json` i.e.
```json
"pathMappings": {
  "/var/www/creighton": "${workspaceRoot}"
}
```

## Resources

* [Acquia Cloud](https://cloud.acquia.com)
* [Acquia Cloud Sitefactory](https://www.creighton.acsitefactory.com/)
