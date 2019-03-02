workflow "CI" {
  on = "push"
  resolves = [
    "Run tests (PHP 5.6)",
    "Run tests (PHP 7.0)",
    "Run tests (PHP 7.1)",
    "Run tests (PHP 7.2)",
    "Run tests (PHP 7.3)",
  ]
}

action "Install dependencies" {
  uses = "pxgamer/composer-action@master"
  args = "install --prefer-dist"
}

action "Run tests (PHP 5.6)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php5.6 vendor/bin/phpspec run"
}

action "Run tests (PHP 7.0)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.0 vendor/bin/phpspec run"
}

action "Run tests (PHP 7.1)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.1 vendor/bin/phpspec run"
}

action "Run tests (PHP 7.2)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.2 vendor/bin/phpspec run"
}

action "Run tests (PHP 7.3)" {
  uses = "franzliedke/gh-action-php@master"
  needs = ["Install dependencies"]
  runs = "php7.3 vendor/bin/phpspec run"
}
