# studio

A workbench for developing Composer packages.

## Installation

Studio can be installed per project or globally, with Composer:

Per project: `composer require franzliedke/studio`

Globally: `composer global require franzliedke/studio`

## Usage

### Create a new package skeleton

    studio create my/package

Packages will be created in a directory named after the package. In above example, the new package folder would be
created in the `package` subdirectory of your application.

### Add a package to the autoloader list

    studio load my/package

This will add the package to a `studio.json` file in your project's root directory.
It's dependencies will then be made autoloadable.
