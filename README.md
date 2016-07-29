# studio

Develop your Composer libraries with style.

This package makes it easy to develop Composer packages while using them.

Instead of installing the packages you're working on from the Packagist repository, use Studio to symlink them from the filesystem instead.
Under the hood, it uses Composer's [path repositories](https://getcomposer.org/doc/05-repositories.md#path) to do so.
As a result, you won't have to develop in the `vendor` directory.

Studio also knows how to configure development tools that might be part of your workflow.
This includes the following:

- Autoloading (`src` and `tests`)
- PhpUnit
- PhpSpec
- TravisCI

This list will only get longer in the future.

## Installation

Studio can be installed globally or per project, with Composer:

Globally (recommended): `composer global require franzl/studio`
(use as `studio`)

> Make sure that the ~/.composer/vendor/bin directory is added to your PATH, so that the `studio` executable can be located by your system.

Per project: `composer require --dev franzl/studio`
(use as `vendor/bin/studio`)

## Usage

All commands should be run from the root of your project, where the `composer.json` file is located.

### General workflow

Studio packages are local directories, which are symlinked to Composer's `vendor` directory for use in a project.

First, we need to create the local directory for the development package:

    $ studio create foo
    # or if you want to clone a git repo
    $ studio create foo --git git@github.com:vendor/package.git
   
This will create a package inside the current working directory under directory `foo`.

Now we need to load it using studio, so studio knows to tell Composer that the development package is available locally.

    $ studio load foo
    
This command should create a `studio.json` file into the current working directory.
It contains mappings to package names and local directories containing said packages.

Now that we have studio packages loaded and ready, we need to tell Composer that we want to use said packages in our current project.
Insert the packages to `composer.json`:

    "require": {
        "bar/foo": "dev-master"
    }
    
Next we run `composer update` and the following happens:

1.  Composer begins checking dependencies for updates.
2.  Studio jumps in and informs Composer about the packages defined in the `studio.json` file.
3.  Composer symlinks studio packages into the `vendor` directory (or in case of installers to their respective installation locations),
    so they behave like "normal" Composer packages.
4.  Composer generates proper autoloading rules for the studio packages.
5.  For non-studio packages Composer works as always.

### Commands

#### Create a new package skeleton

    studio create foo/bar

This command creates a skeleton for a new Composer package, already filled with some helpful files to get you started.
In the above example, we're creating a new package in the folder `foo/bar` in your project root.
All its dependencies will be available when using Composer.

During creation, you will be asked a series of questions to configure your skeleton.
This will include things like configuration for testing tools, Travis CI, and autoloading.

#### Manage existing packages by cloning a Git repository

    studio create bar --git git@github.com:me/myrepo.git

This will clone the given Git repository to the `bar` directory and install its dependencies.

#### Import a package from an existing directory

    studio load baz

This will make sure the package in the `baz` directory will be autoloadable using Composer.

#### Remove a package

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
