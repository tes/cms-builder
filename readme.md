
## One-time tasks
### Requirements

* [Composer](https://getcomposer.org/)
* [Docker](https://www.docker.com/)
* [Platform CLI]
* [Drush]

Note that after installing Docker you have to start the application to complete the command line install

### Installation
You may download a ready-to-use version as a Phar:

```sh
$ curl -LSs https://github.com/tes/cms-builder/raw/master/cms-builder.phar -o cms-builder
```

The command will download it to the current directory. From there, you may place it anywhere that will make it easier for you to access (such as `/usr/local/bin`) and chmod it to `755`.

## Usage
Clone a project from github and run the `cms-builder build` command, for example:
```bash
git clone git@github.com:tes/cms-the-platform.git
cd cms-the-platform
cms-builder build
```

Once the build is complete you can open the site in a browser by doing:
```bash
cms-builder link
```


Profit!!!
