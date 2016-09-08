# SphinxConfig
A ZF2 module to control over sphinxsearch configuration

## Introduction

SphinxConfig is a module for Zend Framework 2 to build and to control over configuration files of SphinxSearch servers.

## Installation

#### By cloning project

    Clone this project into your ./vendor/ directory.

#### With composer

    Add this project in your composer.json:

    "require": {
        "gpskomsa/sphinx-config": "dev-master"
    }

    Now tell composer to download SphinxConfig by running the command:

    $ php composer.phar update

#### Post installation
Enabling it in your application.config.phpfile.

    <?php
    return array(
        'modules' => array(
            // ...
            'SphinxConfig',
        ),
        // ...
    );

## Options

The SphinxConfig module has some options to allow you to customize the basic functionality. After installing SphinxConfig, copy ./vendor/gpskomsa/sphinxconfig/config/sphinx-config.global.php.dist to ./config/autoload/sphinx-config.global.php and change the values as desired.

The following options are available:

- **config_id** - current config id, in fact, the name of configuration files from wich the data will be loaded by default.
- **config_dir** - the directory that contains file structure with configuration files
- **render_dir** - the directory to put in generated config files in `sphinx` format

