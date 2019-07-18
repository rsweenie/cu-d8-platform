## For more info on configuring tugboat, see:

* https://docs.tugboat.qa/

## Note on Acquia integration

In order to pull the DB and assetts from Acquia, we run a couple of Drush
commands in the Tugboat build process. This requires SSH key setup between
Tugboat and Acquia.

One developer should configure this once, and it will work for everyone. If that
developer's keys become invalid for whatever reason, you will probably seen an 
error similar to this:

```` 
[error]  The command could not be executed successfully (returned: Warning: Permanently added 'web-1747.enterprise-g1.hosting.acquia.com,52.91.59.211' (RSA)
to the list of known hosts.
Permission denied (publickey).  , code: 255)
[error]  The external command could not be executed due to an application error.
````

In this case, you will need to generate a new key pair (or use an existing),
upload the private key to Tugboat, and upload the public key to his individual
account on Acquia Cloud. It can take up to ten minutes for the key to
propogate on Acquia.

To upload a private key in Tugboat, go to:

  My projects / 'project name' / Project Settings / (Repository) Settings


