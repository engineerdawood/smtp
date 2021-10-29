#!/bin/bash

if [ -n "$(which apt-get)" ]
then
    sudo apt-get update

    echo "Installing Spamassassin"
    sudo apt-get install spamassassin

    echo "Installing beanstalkd"
    sudo apt-get install beanstalkd

    echo "Installing supervisor"
    sudo apt-get install supervisor

elif [ -n "$(which yum)" ]
then
    sudo yum update

    echo "Installing Spamassassin"
    sudo yum install spamassassin

    # install EPEL repository first
    sudo yum install epel-release

    # install python-pip
    if [ -n "$(which pip)" ]
    then
        sudo pip install --upgrade pip
    else
        sudo yum -y install python-pip
    fi

    echo "Installing beanstalkd"
    wget http://cbs.centos.org/kojifiles/packages/beanstalkd/1.10/2.el6/x86_64/beanstalkd-1.10-2.el6.x86_64.rpm
    sudo mv beanstalkd-1.10-2.el6.x86_64.rpm /tmp/beanstalkd-1.10-2.el6.x86_64.rpm
    sudo rpm -ivh /tmp/beanstalkd-1.10-2.el6.x86_64.rpm
    sudo rm /tmp/beanstalkd-1.10-2.el6.x86_64.rpm

    echo "Installing supervisor"
    sudo pip install supervisor

    mkdir -p /etc/supervisord/conf.d
    echo_supervisord_conf > /etc/supervisord/supervisord.conf
    echo "files = conf.d/*.conf" >> /etc/supervisord/supervisord.conf

fi

echo "Configuring supervisor"

cp ./mailer-worker.conf /etc/supervisor/conf.d/mailer-worker.conf

echo "Setup completed successfully"
