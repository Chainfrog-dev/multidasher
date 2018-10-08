# Welcome to MultiDasher

## What is MultiDasher?

[MultiDasher](http://www.multidasher.org/) is an open source admin dashboard for [MultiChain](http://www.multichain.com/) blockchains, based on [Drupal 8](http://www.drupal.org/). Drupal and the Adminimal theme provide a GUI for managing and interacting with permissioned MultiChain blockchains, and the content management capabilities of Drupal allow for a more visual and intuitive experience with chains, and for other extensions to be added to the system.

MultiDasher is developed, tested and designed to be run on Ubuntu 18.04.

## Getting Started

The following instructions should have you up and running with MultiDasher on an Ubuntu 18.04 machine within minutes.

If you want to play around with the system, as enabling a MultiDasher instance to connect out through personal firewalls and ensuring IP routing is conducted correctly requires some networking knowledge, we recommend either using a cloud instance of Ubuntu, or running a virtual box on your machine.

Amazon Web Services provides free cloud servers for personal use. See the Wiki for [AWS Setup Instructions]( https://github.com/Chainfrog-dev/multidasher/wiki/AWS-Setup-Instructions).

Other cloud service providers exist, for example DigitalOcean and Microsoft Azure.

If you want to use MultiDasher on your own machine, we recommend a virtual box, for example using Vagrant. See the Vagrant Setup Instructions for a step by step guide. (See the [Vagrant Setup Instructions](https://github.com/Chainfrog-dev/multidasher/wiki/Vagrant-Setup-Instructions) in the Wiki for more details).

1. Log into your instance, and clone this repository into your working directory, for example:

        $ cd Git # mkdir it first if you don't have one
        $ git clone https://github.com/Chainfrog-dev/multidasher.git
        $ cd multidasher
        
2. Run the prerequisites checker and installer. This will install MultiChain and other libraries required for MultiDasher if they are not already present on your system

        $ sudo ./multidasher_server.sh
        
3. Do some Drupal stuff

4. Go to `http://localhost:80` and you'll be asked to create an administrator account.

5. Follow the guidelines to start interacting with MultiChain blockchains. You may find
   the following resources handy:
    * [The MultiDasher wiki](https://github.com/Chainfrog-dev/multidasher/wiki)

## Contributing

We would love your input on this project, whether it be code contributions, opinions on features, project priorities, testing, documentation or UX and graphic design (see CONTRIBUTING.md). Or indeed anything else that you think might help.

Everyone interacting in MultiDasher's codebase, issue tracker, wiki, and mailing list is expected to follow the code of conduct (see CODE_OF_CONDUCT.md).

## License

MultiDasher is released under the [GPLv3](http://www.gnu.org/licenses/gpl.html) license, and the code is copyright [Chainfrog Oy](http://www.chainfrog.com/).
