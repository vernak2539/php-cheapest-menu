# The Cheapest Menu
When I was interviewing for jobs I had to complete a few coding tests to show I had the necessary skills for the position. I completed this coding challenge, but since it didn't accept input via command line, I was not even considered for the position. It works 100% if it's executed inside of a browser, and it wouldn't take too much to make it work via php and commandline.

So this is me posting my solution. But the company it was created for will remain anonymous. There may be a few bugs if you try to be clever and break it intentionally, but for the most part it works (made it in a few hours).

## What's the problem
You need to find a place to eat. But you're feeling cheap and don't want to spent too much money. You want to find out what restaurant is the cheapest based on what you want to eat.

## The Menu Format?
The menus are CSV files. They are set up in the following format:

    X, Y, Z
    
Where:
* X = restaurant key
* Y = price
* Z = item name

**Example Menu (3 in repo)**

    1, 4.00, ham_sandwich
    1, 8.00, burrito
    2, 5.00, ham_sandwich
    2, 6.50, burrito

## Using the PHP class
The `index.php` file is the example of how to use this class. If you don't want to look, here's the basics
```php
    // including class
    include("menuClass.php");

    // initializing PHP class from one of the menus in the repo
    new Menu('menu3.csv', array('fish_sandwich', 'milkshake'));

    // expected output
    // 6, 11.0
```