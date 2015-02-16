# studio

**STILL IN DEVELOPMENT**

Develop your Composer libraries with style.

This package makes it easy to develop Composer packages while using them.

Instead of installing the packages you're actively working on as a dependency, use Studio to manage your libraries.
It will take care of autoloading your library's dependencies, and you won't have to develop in the `vendor` directory.

## Installation

Studio can be installed per project or globally, with Composer:

Per project: `composer require --dev franzliedke/studio:@dev`
(use as `vendor/bin/studio`)

Globally: `composer global require franzliedke/studio:@dev`
(use as `studio`)

## Usage

### Create a new package skeleton

    studio create foo

This command creates a skeleton for a new Composer package, already filled with some helpful files to get you started.
In the above example, we're creating a new package in the folder `foo` in your project root.
All its dependencies will be available when using Composer.

### Manage existing packages by cloning a Git repository

    studio create bar --git git@github.com:me/myrepo.git

This will clone the given Git repository to the `bar` directory and install its dependencies.
