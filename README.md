# Welcome to MultiDasher

## What is MultiDasher

[MultiDasher](http://www.multidasher.org/) is an open source admin dashboard for [MultiChain](http://www.multichain.com/) blockchains, based on [Drupal 8](http://www.drupal.org/). Drupal and the Adminimal theme provide a GUI for managing and interacting with permissioned MultiChain blockchains, and the content management capabilities of Drupal allow human-readible names to be associated with blockchain addresses, and for other extensions to the system.

MultiDasher is developed, tested and designed to be run on Ubuntu 18.04.

## Getting Started

The following instructions should have you up and running with MultiDasher on your Ubuntu 18.04 machine within minutes. If you want to play around with the system, we recommend either using a cloud instance of Ubuntu, or running a virtual box on your machine (See the [Vagrant Setup Instructions](https://github.com/Chainfrog-dev/multidasher/wiki/Vagrant-Setup-Instructions) in the Wiki for more details).

1. Clone this repository into your working directory, for example:

        $ cd Git
        $ git clone https://github.com/Chainfrog-dev/multidasher.git
        $ cd multidasher
        
2. Run the prerequisites checker and installer. This will install MultiChain and other libraries required for MultiDasher if they are not already present on your system

        $ ./install.sh
        
3. Do some Drupal stuff

4. Go to `http://localhost:80` and you'll be asked to create an administrator account.

5. Follow the guidelines to start interacting with MultiChain blockchains. You may find
   the following resources handy:
    * [The MultiDasher wiki](https://github.com/Chainfrog-dev/multidasher/wiki)

## Contributing

We are still working on contribution guidelines.

Everyone interacting in MultiDasher's codebase, issue tracker, wiki, and mailing list is expected to follow the code of conduct (see CODE_OF_CONDUCT.md).

## License

MultiDasher is released under the [GPLv3](http://www.gnu.org/licenses/gpl.html) license, and the code is copyright [Chainfrog Oy](http://www.chainfrog.com/).
