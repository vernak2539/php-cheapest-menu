<?php

/*
@author: Alex Vernacchia
@title: Cheapest Restaurant
@date: 10/26/12

INSTRUCTIONS:

To use the following class follow the example below. Some more documentation is before

(object) Menu(param1, param2)

(string) param1
 - must be the path to the menu to search
 - menu must be formatted correctly
  - examples in menu1.csv, menu2.csv, menu3.csv

(array) param2
  - must be array of items (will display nice message if none are given)
  - try to trim values, but class should do it for you


*/

include("menuClass.php");

// example 1
new Menu('menu1.csv', array('ham_sandwich', 'burrito'));

// example 2
new Menu('menu2.csv', array('blt_sandwich', 'coffee'));

// example 3
new Menu('menu3.csv', array('fish_sandwich', 'blue_berry_muffin'));