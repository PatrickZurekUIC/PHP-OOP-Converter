#Object oriented PHP to procedural PHP converter
Converts programs written in Object Oriented PHP to semantically equivalent Procedural code. 

#Installation
First, clone the project from github:

    git clone https://github.com/PatrickZurekUIC/PHP-OOP-Converter.git

Next, if Composer is not installed system-wide, switch to the cloned repository directory and install it into the project by running

    curl -s http://getcomposer.org/installer | php

Then use Composer to install the dependencies:

    php composer.phar install

#Usage
To convert a program, run the following command, it creates a file with the same name in the out directory:

    php transform.php path/to/file out/dir/path
