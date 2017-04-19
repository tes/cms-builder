# How to contribute
## Background
This project relies on heavily on the platform-docker project. Most of the code in cms-builder is about orchestrating a platform-docker build command with TES specific business logic.

To make changes to the docker containers you will need to contribute to the platform-docker project. At the moment we are using a fork of the project: [https://github.com/alexpott/platform-docker](https://github.com/alexpott/platform-docker)

Additionally, platform-docker depends on mglaman/docker-helper and mglamam/toolstack-helper both of these are forked in the same way and are in [https://github.com/alexpott](https://github.com/alexpott)

## How to make changes to this project
1. Clone https://github.com/tes/cms-builder
1. If changing cms-builder follow the usual TES PR workflow

### To make a platform-docker (or other dependency) change
1. Fork [https://github.com/alexpott/platform-docker](https://github.com/alexpott/platform-docker) and clone your fork
1. To make and test a change to platform-docker ensure your working branch branches off from tes branch
1. You can remove vendor/mglaman/platform-docker from the cms-builder checkout and symlink your checkout of platform-docker there
1. Create a PR against [https://github.com/alexpott/platform-docker](https://github.com/alexpott/platform-docker) (the tes branch) to request a platform-docker change.
1. To submit a PR back to [https://github.com/mglamam/platform-docker](https://github.com/mglamam/platform-docker) create a new branch off develop, cherry-pick your commits from your other branch, push and create a PR against matt's project.

If you are making changes to mglaman/docker-helper or mglamam/toolstack-helper follow a similar process as that for platform-docker.

## Building the .phar file
Once the changes have been merged into [https://github.com/tes/cms-builder](https://github.com/tes/cms-builder) and [https://github.com/alexpott/platform-docker](https://github.com/alexpott/platform-docker) (if required), we need to rebuild the .phar file.

Pre-requisite: Install and configure [Box2](https://github.com/box-project/box2)

1. Remove composer.lock
1. Run composer install --no-dev
1. Run box build
1. Commit and push changes to master
