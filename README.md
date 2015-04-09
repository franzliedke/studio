# studio

Develop your Composer libraries with style.

This package makes it easy to develop Composer packages while using them.

Instead of installing the packages you're actively working on as a dependency, use Studio to manage your libraries.
It will take care of autoloading your library's dependencies, and you won't have to develop in the `vendor` directory.

Studio also knows how to configure development tools that might be part of your workflow.
This includes the following:

- Autoloading (src & tests)
- PhpUnit
- PhpSpec
- TravisCI

This list will only get longer in the future.

## Installation

Studio can be installed globally or per project, with Composer:

Globally (recommended): `composer global require franzl/studio`
(use as `studio`)

>Make sure to place the ~/.composer/vendor/bin directory in your PATH so the `studio` executable can be located by your system.

Per project: `composer require --dev franzl/studio`
(use as `vendor/bin/studio`)

## Usage

All commands should be run from the root of your project, where the `composer.json` file is located.

### Create a new package skeleton

    studio create foo/bar

This command creates a skeleton for a new Composer package, already filled with some helpful files to get you started.
In the above example, we're creating a new package in the folder `foo/bar` in your project root.
All its dependencies will be available when using Composer.

During creation, you will be asked a series of questions to configure your skeleton.
This will include things like configuration for testing tools, Travis CI, and autoloading.

### Manage existing packages by cloning a Git repository

    studio create bar --git git@github.com:me/myrepo.git

This will clone the given Git repository to the `bar` directory and install its dependencies.

### Import a package from an existing directory

    studio load baz

This will make sure the package in the `baz` directory will be autoloadable using Composer.

### Remove a package

Sometimes you want to throw away a package.
You can do so with the `scrap` command, passing a path for a Studio-managed package:

    studio scrap foo

Don't worry - you'll be asked for a confirmation first.

## License

This code is published under the [MIT License](http://opensource.org/licenses/MIT).
This means you can do almost anything with it, as long as the copyright notice and the accompanying license file is left intact.

## Contributing

Feel free to send pull requests or create issues if you come across problems or have great ideas.
Any input is appreciated!
