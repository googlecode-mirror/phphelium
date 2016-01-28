## What is Helium? ##

Helium is a light-weight PHP MVC framework that is designed to be painless to install, simple to use, and highly extensible and scalable. It is built for a standard LAMP stack and is installable and configurable from the command line.


## Who made this garbage? ##

The author of this particular mass of code is an engineer named Bryan Healey.


## Why make yet another framework? ##

The genesis of this framework is rooted in utility. I wanted to have an easy to deploy and easy to develop core that wasn't bloated with a litany of extras that I didn't want or need. So, instead of taking an existing framework and stripping it down, I decided to write a framework from the ground up. This allowed me to take care in how it is engineered, maintaining speed and efficiency as much as possible.


## Who can us it? ##

Anyone! Even those without extensive web development backgrounds can use it, so long as they have a modest understanding of PHP. Out of the box, it comes ready for deployment, and the basic method for creating new pages and services couldn't be simpler: Create a route, create a controller, and create a template file. Done.


## What version is most current? ##

The most current version of Helium is v3.2 (released June 28, 2013)


## How do I get Helium? ##

From the command line:
  * wget http://repo.phphelium.com/keys/helium.gpg.key http://repo.phphelium.com/apt/debian/helium.list
  * apt-key add helium.gpg.key
  * mv helium.list /etc/apt/sources.list.d/
  * apt-get install helium-base