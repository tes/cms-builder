# How to test the cms-builder on linux

In a directory somewhere on a host OS create a VM using vagrant.
```bash
git clone git@github.com:tes/cms-builder.git
git clone git@github.com:tes/cms-vision.git
vagrant init ubuntu/xenial64
vagrant up
vagrant ssh
```

Ubuntu has started and you are logged in.

```bash
# Install PHP and maria client
sudo apt-get update
sudo apt-get install curl php7.0 php7.0-curl php7.0-mbstring php7.0-xml php7.0-bcmath php7.0-gd php7.0-intl php7.0-mbstring php7.0-mcrypt php7.0-opcache php7.0-readline php7.0-soap php7.0-zip php7.0-mysql mariadb-client

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --filename=composer
sudo mv composer /usr/local/bin

# Install platform
curl -sS https://platform.sh/cli/installer | php
source .profile

# Install docker
sudo apt-get install     apt-transport-https     ca-certificates     curl     software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
sudo apt-get update
sudo apt-get install docker-ce
sudo usermod -aG docker $USER

# Install docker-compose
sudo curl -L "https://github.com/docker/compose/releases/download/1.11.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Install drush
composer global require drush/drush
sudo ln -sfn ~/.config/composer/vendor/bin/drush /usr/local/bin/drush

# Need to reboot the VM because of adding the user to the docker group.
exit
```

Back on the HOST OS.
```bash
vagrant halt
vagrant up
vagrant ssh
```

Ubuntu has started again and you are ready to do your first build
```bash
# Copy the files. Permissions and the vagrant share are not fun.
cp -R /vagrant/cms-vision ./
cd cms-vision
/vagrant/cms-builder/cms-builder.phar -vvv build
```
