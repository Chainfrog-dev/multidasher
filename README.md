# Welcome to MultiDasher

## What is MultiDasher?

[MultiDasher](http://www.multidasher.org/) is an open source admin dashboard for [MultiChain](http://www.multichain.com/) blockchains, based on [Drupal 8](http://www.drupal.org/). And much, much more.

## Architecture

In MultiDasher:

* Angular is used to present a graphical frontend to the system. If you prefer a different presentation layer, you are welcome to replace it with your own.

* A decoupled Drupal installation functioning as a backend system provides content management and rich metadata for the blockchain assets (cryptocoins and tokens) and data streams, effectively adding "meaning" to the system.

* MultiChain (as a blockchain component of the system) acts an enhanced transport layer by supporting peer-to-peer connectivity, irrefutable ownership of unique unforgeable digital assets, and reliable tamper-proof data synchronization through data streams.

MultiDasher is developed, tested and designed to be run on Ubuntu 18.04.

## Getting Started

If you want to have a look at how MultiDasher functions, please visit [the MultiDasher website](https://multidasher.org/) and click on Demo.

The [Wiki](https://github.com/Chainfrog-dev/multidasher/wiki) has a lot of resources too, and is worth browsing through.

### Installation

The following instructions should have you up and running with MultiDasher on an Ubuntu 18.04 machine within minutes. How many minutes depends on your knowledge of Drupal and IP networking.

Enabling a MultiDasher instance to connect out through personal firewalls and ensuring IP routing is conducted correctly requires some networking knowledge, so we recommend either using a cloud instance of Ubuntu, or running a virtual box on your machine.

Amazon Web Services provides free cloud servers for personal use. See the Wiki for [AWS Setup Instructions]( https://github.com/Chainfrog-dev/multidasher/wiki/AWS-Setup-Instructions).

Other cloud service providers exist, for example DigitalOcean and Microsoft Azure.

If you want to use MultiDasher on your own machine, we recommend a virtual box, for example using Vagrant. See the Vagrant Setup Instructions for a step by step guide. See the [Vagrant Setup Instructions](https://github.com/Chainfrog-dev/multidasher/wiki/Vagrant-Setup-Instructions).

To enable secure HTTPS connectivity (strongly recommended) you will need to set up an A Host for a domain or subdomain to point at your server instance, as HTTPS requires digital certificates that are linked to domain names. See [Domain Names and Digital Certificates](https://github.com/Chainfrog-dev/multidasher/wiki/Domain-Names-and-Digital-Certificates).

1. Log into your instance, and clone this repository into your working directory, for example:

        $ cd Git # mkdir it first if you don't have one
        $ git clone https://github.com/Chainfrog-dev/multidasher.git
        $ cd multidasher
        
2. Run the prerequisites checker and installer. This will install MultiChain and other libraries required for MultiDasher if they are not already present on your system

        $ sudo ./multidasher_server.sh
        
3. Read the instructions that are shown at the beginning of running the install script, and follow them carefully.

4. Go to `http://localhost:80` and you'll be asked to create an administrator account.

5. Follow the guidelines to start interacting with MultiChain blockchains. You may find
   the following resources handy:
    * [The MultiDasher wiki](https://github.com/Chainfrog-dev/multidasher/wiki)

## Contributing

We would love your input on this project, whether it be code contributions, opinions on features, project priorities, testing, documentation or UX and graphic design (see CONTRIBUTING.md). Or indeed anything else that you think might help.

Everyone interacting in MultiDasher's codebase, issue tracker, wiki, and mailing list is expected to follow the code of conduct (see CODE_OF_CONDUCT.md).

## License

MultiDasher is released under the [GPLv3](http://www.gnu.org/licenses/gpl.html) license, and the code is copyright [Chainfrog Oy](http://www.chainfrog.com/).
