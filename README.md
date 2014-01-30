# m3da2glass-demo

Basic demo that sends m2m.eclipse.org M3DA data to Google Glass, based on an older version of the starter project.

## What's in the box

* /web - simple authentication frontend based on Google Glass PHP starter project
* /db - where the sqlite database is created
* /modules - stuff we need for installing VM packages with Puppet
* /manifests - Puppet configuration and classes
* /Vagrantfile - base configuration for our Vagrant virtual machine

## Vagrant and Puppet

This example uses Vagrant (http://www.vagrantup.com/) and Puppet (https://puppetlabs.com/) to setup a virtual machine that runs the both the web frontend for authentication and backend service script. This makes this project pretty easy to get up and running!

## Configuration

1. Edit the configuration in /web/config.php:
```
$api_client_id = "YOUR_CLIENT_ID";
$api_client_secret = "YOUR_CLIENT_SECRET";
$api_simple_key = "YOUR_SIMPLE_KEY";
```

2. Get the Precise64 Vagrant box:
```
$ vagrant box add precise64 http://files.vagrantup.com/precise64.box
```

3. Fire up the virtual machine:
```
$ vagrant up
```

4. Navigate to the authentication page at http://localhost:4040 and log in.

5. Accept the permissions, and watch as M2DA is polled and sent to your Glass.

## Not for production, ala, watch the dust and the quota!

I wrote this demo pretty quick (5 minutes or so). This is a barebones poller; it doesn't do any checks that you'd normally do. I wrote it just to play and took no precautions.
 
In a I'm-writing-for-production senarion, what should be happening:

1. You should be comparing the value set to previous; if things haven't changed, then don't send an update

2. You should be storing the timelineid and updating the card, not inserting a new one everytime

3. I don't like hard polling a broker; it sort of defeats the purpose but it does work.

4. Seriously, the libs with services + offline tokens + Mirror API = win: http://m2m.eclipse.org/

:-)