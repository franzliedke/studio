workflow "CI" {
  on = "push"
  resolves = ["Run tests (PHP 7.1)"]
}

action "Install dependencies" {
  uses = "pxgamer/composer-action@master"
  args = "install --prefer-dist"
}

action "Run tests (PHP 7.1)" {
  needs = ["Install dependencies"]
  uses = "franzliedke/gh-action-php@master"
  runs = "php7.1 vendor/bin/phpspec run"
}
