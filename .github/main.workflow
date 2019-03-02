workflow "CI" {
  on = "push"
  resolves = ["PhpSpec tests"]
}

action "Install dependencies" {
  uses = "pxgamer/composer-action@master"
  args = "install --prefer-dist"
}

action "PhpSpec tests" {
  uses = "sh"
  needs = ["Install dependencies"]
  args = "vendor/bin/phpspec run"
}
