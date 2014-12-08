#Object oriented PHP to procedural PHP converter
Converts programs written in object oriented PHP to their procedural equivalent.


#Installation
First, clone the project from github:

    git clone https://github.com/PatrickZurekUIC/PHP-OOP-Converter.git

Next, if Composer is not installed system-wide, switch to the cloned repository directory and install it into the project by running

    curl -s http://getcomposer.org/installer | php

Then use Composer to install the dependencies:

    php composer.phar install

#Usage
To convert a program and pipe the output to stdout run (without the < >'s):
    php parse <program_name.php> -

To convert a program and write it to a filenamed output.php, run:
    php parse <program_name.php> output.php
