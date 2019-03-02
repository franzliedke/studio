workflow "CI" {
  on = "push"
  resolves = ["Install dependencies"]
}

action "Install dependencies" {
  uses = "pxgamer/composer-action@master"
  args = "install --prefer-dist"
}
