# Jade

Jade provides a standard set of tools for lawyers to manage their cases and clients.

## Features

* Browser-based dashboard for client and case management
* Electronic, searchable repository of documents and other case artifacts
* Consolidated time recording and reporting system
* Desktop and mobile access
* Support for multiple languages

[Screenshots and more information](https://www.slideshare.net/vvaswani/jade-10-2017-80571396)

## Requirements

* PHP 5.6 or PHP 7.x with `intl` and `pdo` extensions
* Apache 2.x
* MySQL 5.x
* Git

## Installation

* If you have [Docker](https://docker.com/) and [Docker Compose](https://docs.docker.com/compose/), install the application with the following commands:

		    cd /tmp
		    curl https://raw.githubusercontent.com/vvaswani/jade/master/.docker/docker-compose.yml > docker-compose.yml && docker-compose up

	Browse to http://DOCKER-HOST:8080/ to access the application.

* If you don't have [Docker](https://docker.com/) and [Docker Compose](https://docs.docker.com/compose/) but have an environment with all the components listed above, install the application into your existing environment using this [brief guide](docs/INSTALL_ALL.md).

* If you don't have [Docker](https://docker.com/), [Docker Compose](https://docs.docker.com/compose/) or the components listed above, refer to the [Windows](docs/INSTALL_WINDOWS.md), [Linux](docs/INSTALL_LINUX.md) or [macOS](docs/INSTALL_MACOS.md) guides for detailed, platform-specific instructions on how to set up the necessary environment and install the application.

## Roadmap

If you are interested in the future direction of this project, please [review the roadmap](https://github.com/vvaswani/jade/milestones) or contribute using the [issues log](https://github.com/vvaswani/jade/issues). Your feedback is appreciated.

## Useful Resources

* [Public demo](http://jade.melonfire.com)
* [Project status](https://waffle.io/vvaswani/jade)
* [User stories](https://github.com/vvaswani/jade/issues?q=is%3Aopen+is%3Aissue+label%3Astory)
* [Screenshots](https://www.slideshare.net/vvaswani/jade-10-2017-80571396)

## Disclaimer

"Jade" is a working name for this project. The final name is yet to be determined (see [issue #54](https://github.com/vvaswani/jade/issues/54)).