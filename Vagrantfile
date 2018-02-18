# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "debian/jessie64"

  config.vm.synced_folder "./",
    "/etc/windcentrale",
    type:"nfs",
    :nfs => true,
    :mount_options => ['actimeo=2']

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "512"
  end

  config.vm.network "private_network", ip: "192.168.20.20"
  config.ssh.forward_agent = true

  config.vm.provision "shell", inline: <<-SHELL
     apt-get install -y apt-transport-https lsb-release ca-certificates curl git unzip
     wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
     curl -sL https://repos.influxdata.com/influxdb.key | sudo apt-key add -
     curl https://packagecloud.io/gpg.key | sudo apt-key add -
     echo "deb https://repos.influxdata.com/debian jessie stable" | sudo tee /etc/apt/sources.list.d/influxdb.list
     echo "deb https://packagecloud.io/grafana/stable/debian/ jessie main" | sudo tee /etc/apt/sources.list.d/grafana.list
     echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main"  | sudo tee /etc/apt/sources.list.d/php.list
     apt-get update
     apt-get install -y libmosquitto-dev php7.2 php7.2-dev php7.2-curl php-pear influxdb grafana
     influx -execute 'CREATE DATABASE windcentrale'
     pear config-set php_ini /etc/php/7.2/cli/php.ini
     pecl config-set php_ini /etc/php/7.2/cli/php.ini
     pecl install Mosquitto-alpha
     curl -Ss https://getcomposer.org/installer | php
     sudo mv composer.phar /usr/bin/composer
     systemctl daemon-reload
     systemctl enable grafana-server.service
     systemctl start influxdb
     systemctl start grafana-server
  SHELL
end
