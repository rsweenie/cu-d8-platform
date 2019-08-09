# Intro

## Requirements

In order to use this lando configuration, you will need to install Docker and
Lando. The Lando installer will generally install Docker for you. 

If you're on a PC, all of this will probably not work. We have tried it using
WSL and native Lando/Docker. The workaround is to build a Linux virtual machine 
and work within that environment, which *will* work.

## Basic useage

Optionally, you can copy the file projectdir/lando/example.lando.local.yml to
projectroot/.loando.local.yml and add any customizations for you particular use
case. This is not required, however.

If everything is working properly, simply run `lando start`. The first time this
is run, it will set up your environment and may take five to ten minutes. Once
you're environment is built, use the standard lando commands (to see them,
run `lando` with no arguments).

# Lando Commands

External tools can be integrated into the Lando container from externally. You
can also

## BLT

The Acquia BLT command has been integrated into lando. It can be run either
when ssh'd into the lando container or from the host environment by running
`lando blt`

## `fixssh`

This is a virtual script defined in the main .lando.yml configuration file.

In order to sync database and assets with Acquia, the lando instanct must
have access to your Acquia public and private keypair. When the environment
is built, these files will be copied from your ~/.ssh dir and into the lando
container automatically. If for some reason you begin to see SSH errors
regarding keys and key permissions, you can run `lando fixssh` and it will
again copy these files in and set the appropriate permissions.

It is always assumed that the following files exist in your home directory
and that you have uploaded your public key to Acquia. NOTE: it can take up to
ten minutes for Acquia to propogate your private key once you have uploaded it.

<pre>
  ~/.ssh/id_rsa 
  ~/.ssh/id_rsa.pub
</pre>

## Other built in commands

You can also run the commands `composer` and `drush` in the same way you would
run the previously described commands.

# More information

* [Lando overview](https://docs.lando.dev/basics/)
* [Lando configuration](https://docs.lando.dev/config/lando.html)
* [Docker](https://www.docker.com/)

