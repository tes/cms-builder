
## One-time tasks
### Requirements

* [Composer](https://getcomposer.org/)
* [Docker](https://www.docker.com/)
* [Platform CLI]
* [Drush]?
* [Docker Compose](https://docs.docker.com/compose/) (Comes with the Docker App but you have to launch the app to install it)
* [Platform Docker](https://github.com/mglaman/platform-docker)

Note that on OSx after installing Docker you have to start the application to
commplete the command line install
### Set up
dnsmasq (OS X, Linux, Windows)
`address=/platform/127.0.0.1`

### Installation
curl https (copy from composer)

## Usage
Clone a project from github and run the `tes-install init` command, for example:
```bash
git clone git@github.com:tes/cms-the-platform.git
cd cms-the-platform
cms-builder build
```

Visit
 

Profit!!!

### Notes

Manual steps
```bash
git clone git@github.com:tes/cms-the-platform.git
cd cms-the-platform
platform build
platform-docker init
Now to get the DB and do it with drush
```