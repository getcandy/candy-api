# Hex Candy API

Please see the (Candy API original repo)[https://github.com/getcandy/candy-api]
for information about this API, installation instructions and support.

## Updating this fork

When a new version of Candy is released and we want to update, there is a small
process to follow:

- Clone this repo locally

- Add a new remote for the original API, called `upstream`:

```bash
git remote add upstream git://github.com/getcandy/candy-api.git
```

- Pull in the latest version from `upstream` and merge into the `master` branch:

```bash
git pull upstream master && git pull upstream master --tags
```

- Delete the tag for the most recent version (check the GitHub releases for the)
latest release, or run `git describe --tags`

```bash
git tag -d 0.2.77 # Replace this with the actual latest tag
```

- Checkout the `production` branch, and merge our new `master` in to it

```bash
git checkout production && git pull origin production && git merge master
```

- Finally, tag this new commit on the `production` branch, and push to origin

```bash
git tag -a 0.2.77 -m "0.2.77" && git push origin production --follow-tags
```
