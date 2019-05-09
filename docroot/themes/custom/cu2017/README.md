# Drupal 8 cu2017 Web Theme

## Bootstrap-Sass subtheme that uses Compass to compile Sass

### Dependencies

- Ruby

### Install Sass and Compass

    gem install compass

### Compile Sass

    Compile on save: compass watch

    Compile on command: compass compile

### Flush Cache

    It's Drupal. Just do it.

### Linting

    This theme incorporates Sass-Lint. Documentation on Sass-Lint rules can be found at https://github.com/sasstools/sass-lint/tree/master/docs/rules

### Theme Quirks

We are using fitty v2.2.6 to prevent text wrapping of things like email addresses. Unfortunately, the non-minified 
version that's provided in the source tree does not work, so we are including the minified version instead.
