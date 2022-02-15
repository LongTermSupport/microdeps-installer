# MicroDeps/Installer

MicroDeps are all about very small pieces of code that do a single small thing well

This library facilitates installing MicroDeps into your project with a single command.

__*This is very much Beta stability at the moment*__

The concept is something of an idea that I am exploring and feedback would be gratefully received.

## Usage

First of all you need to install this library alongside an actual MicroDep

For example:

```bash
# install the Curl MicroDep
composer require --dev lts/microdeps-curl dev-master@dev

# install this installer
composer require --dev lts/microdeps-installer dev-master@dev

```

Then you can install the microdep into your project like this:

```bash
./vendor/bin/md vendorDir=./vendor/lts/microdeps-curl/
```

This will copy the src and test files into your project, and will update namespaces in the files as required.

Once the code has been copied into your project, you are free to update it and extend it as you see fit. Should you wish
to push/suggest any of your changes in the original MicroDep (please do) then you will need to create a fork and pull
request as normal.

### PHP QA CI

This package is using PHP QA CI for the quality assurance and continuous integration. You can read more about that here:
https://github.com/LongTermSupport/php-qa-ci

#### To run QA process locally

To run the full QA process locally, simply run:

```bash
./bin/qa
```

## Long Term Support

This package was brought to you by Long Term Support LTD, a company run and founded by Joseph Edmonds

You can get in touch with Joseph at https://joseph.edmonds.contact/

Check out Joseph's recent book [The Art of Modern PHP 8](https://joseph.edmonds.contact/#book)