
## One-time tasks
### Requirements

* PHP
* Mysql client
* [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
* [Composer](https://getcomposer.org/)
* [Docker](https://www.docker.com/)
* [Platform CLI](https://docs.platform.sh/overview/cli.html)
* [Drush](http://docs.drush.org/en/master/install/)
* Docker Compose

Note that after installing Docker you have to start the application to complete the command line install

### Installation
You may download a ready-to-use version as a Phar:

```sh
$ curl -LSs https://github.com/tes/cms-builder/raw/master/cms-builder.phar -o cms-builder
```

The command will download it to the current directory. From there, you may place it anywhere that will make it easier
for you to access (such as `/usr/local/bin`) and chmod it to `755`.

### Update
You can run the self-update command.
```sh
cms-builder self-update
```

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

After a reboot/restart of your machine, to start the group of containers represented in docker-compose.yml in your project root:
```bash
cms-builder docker:up
```
(This will result in a different link, for the access of the site in the browser, to the one you used before the restart.)

To list cms-builder available commands:
```bash
cms-builder list
```

## Setting up a new project
Add this to the project's .gitignore
```
# ignore cms-builder artifacts
.cms-builder/
docker/
.platform-project
docker-compose.yml
```

Create a .cms-builder.yml file in the project root. Example contents:
```yaml
database: http://jenkins-native.tescloud.com/view/CMS/job/cms-backup-tes-live/ws/database.sql.gz
post_build:
  bash:
    # Bash commands in the post_build are run from the root directory of the project
    - 'cd _www/sites/all/modules/shared && rm -rf cms_modules && git clone git@github.com:tes/cms-modules.git cms_modules'
  docker:
    solr:
      - 'rm -rf /opt/solr/example/solr/tes_core'
      - 'mkdir /opt/solr/example/solr/tes_core'
      - 'echo name=tes_core > /opt/solr/example/solr/tes_core/core.properties'
      - 'cp -r /opt/solr/example/solr/collection1/conf /opt/solr/example/solr/tes_core'
  drush:
    - 'en stage_file_proxy devel -y'
```

## Troubleshooting
### Permission denied when connecting to socket?
```bash
sudo usermod -a -G docker $USER
```
### Files keep reappearing even though you've deleted them?
The cms-builder uses unison to sync files to the container. This is a performance tweak for OSX. Sometimes the volume
can get out-of-sync and contain files you don't want anymore. Running the following command with cause the volume to be
rebuilt:
```bash
cms-builder build --rebuild-volumes
```
### Don't run the command with sudo
Just don't.

### Error access pages using Solr? (e.g: /student/what-to-study/accounting-finance)
> SearchApiException: "0" Status: Request failed: Cannot assign requested address in SearchApiSolrConnection->checkResponse() (line 547 of /var/platform/.platform/local/builds/default/public/sites/all/modules/contrib/search_api_solr/includes/solr_connection.inc).
```bash
cms-builder post-build
```

### Cms-builder build or post-build fails

If you think the containers might have issues, check whether they're running:

```bash
docker ps
```

If they aren't running, try to get them running:
```bash
cms-builder docker:up
```

If this fails, make sure the containers are stopped:
to stop Docker:
```bash
cms-builder docker:stop
```
Now you can try to remove the docker directory (Be careful!: this is where shared directories and files go including the database!!!):
```bash
rm -rf docker
```
Then build again:
```bash
cms-builder build
```
