<?php

class Menu {

  private $_wanted_items;
  private $_individual_items = array();
  private $_combo_items      = array();
  private $_menu             = array();
  private $_final_prices;

  public function __construct($file, $food_array = array()) {
    if(!$this->checkForItems($food_array)) 
      exit();
    if(!$this->checkForMenu($file))
      exit();
    $this->getMenuContent($file);
    $this->sortMenu();
    $this->sortByRest();
    $this->checkMenuIndividualItems();
    $this->addIndividualPrices();
    $this->processComboItems();
    $this->determineCheapestPlace();

    exit();

  }

  // making sure the user actually wants to get food
  private function checkForItems($food_array) {
    if(count($food_array) != 0) {
      $this->_wanted_items = $food_array;
      $this->trimWantedItems();
      return true; // just incase wanting to use this check elsewhere
    } else {
      echo 'You must be hungry. Try again, it\'s almost lunchtime.';
      return false; // just incase wanted to use this check elsewhere
    }
  }

  // trims whitespace off inputed values from user. Could have been done more elegantly (without trim array), but I was in a hurry to get this back to you. Next time haha
  private function trimWantedItems() {
    $temp = array();
    foreach($this->_wanted_items as $item) {
      $temp[] = trim($item);
    }
    $this->_wanted_items = $temp;
  }

  // checking if file string is not empty and if file has a size (so basically checking if they want to eat somewhere)
  private function checkForMenu($file) {
    if(empty($file) || filesize($file) == 0) {
      echo 'Where are you going? I need a menu, you do to probably. Try again.';
      return false;
    } else {
      return true;
    }
  }

  // parsing csv file to get menu
  private function getMenuContent($menu) {
    $handle = fopen($menu, 'r');
    while(($data = fgetcsv($handle, 5000, ',')) !== FALSE) {
      $this->_menu[] = $data;
    }
    return true;
  }

  // sorts menu items based on individual items and combos
  private function sortMenu() {
    foreach($this->_menu as $item) {
      if(count($item) > 3) {
        $this->_combo_items[] = $this->formatCombo($item);
      } else {
        $this->_individual_items[] = $this->formatIndividual($item);
      }
    }
    return true;
  }

  // formatting new array item for individual item and returning it
  private function formatIndividual($item) {
    $menu_entry['id']     = (int)trim($item[0]);
    $menu_entry['price']  = (float)trim($item[1]);
    $menu_entry['items']   = array(trim($item[2]));
    return $menu_entry;
  }

  // formatting new array item for combo item and returning it
  private function formatCombo($item) {
    $menu_entry['id']     = (int)trim($item[0]);
    $menu_entry['price']  = (float)trim($item[1]);
    for($i = 2; $i < count($item); $i++) {
      $items[] = trim($item[$i]);
    }
    $menu_entry['items'] = $items;
    return $menu_entry;
  }

  // further sorting array into items by resturants (unqiue ids) Only Individual Items. Take care of combo items later
  private function sortByRest() {
    $ids = $this->getUniqRestId();
    foreach($this->_individual_items as $item) {
      $item = (object)$item;
      $sorted[$item->id][] = array($item->items, $item->price);
    }
    $this->_individual_items = $sorted;
  }

  // getting unique restaurant ids from array and returning them to above function
  private function getUniqRestId() {
    foreach($this->_individual_items as $item) {
      $item = (object)$item;
      $ids[] = $item->id;
    }
    return array_unique($ids);
  }

  // checking menus if they have all the items requested
  private function checkMenuIndividualItems() {
    // looping through restaurants
    foreach($this->_individual_items as $key => $value) {
      // looping through all items in restuarant
      foreach($value as $item) {
        // checking if  items match one of the wanted items
        $total[$key][] = $this->checkItemsForWanted($item);
      }
    }
    $this->_final_prices = $total;
    $this->sortFinalPrices();
  }

  // returns price of item if it wanted by user and on menu. Returns false if not
  private function checkItemsForWanted($item) {
    // cycle through items wanted
    for($z = 0; $z < count($this->_wanted_items); $z++) {
      for($j = 0; $j < count($item[0]); $j++) {
        if($this->_wanted_items[$z] == $item[0][$j]) {
          //$price = array($item[1], implode(', ', $item[0]));
          $price = $item[1];
        } 
      }
    }
    return (empty($price)) ? "false" : $price;
  }

  // sorting arrays to put "false" at end for future calulation
  private function sortFinalPrices() {
    foreach($this->_final_prices as $key => $value) {
      arsort($value);
      $temp[$key] = $value;
    }
    $this->_final_prices = $temp;
  }

  // adding up the individual items requested for each resturant to obtain a total for those items
  private function addIndividualPrices() {
    $final_list = $this->checkNumWantedVNumFound();
    if(count($final_list) != 0) {
      //var_dump($final_list);
      $sum  = 0;
      foreach($final_list as $key => $res) {
        foreach($res as $price) {
          $sum += $price;
        }
        $sums[$key] = $sum;
        $sum = 0;
      }
    }
    if(isset($sums)) {
      $this->_final_prices = $sums;
    } else {
      $this->_final_prices = array();
    }
  }

  // checks to see if count of wanted items is equal to the count of items found on the menu (Individual items specifically)
  private function checkNumWantedVNumFound() {
    $ctr = 0;
    $final_array = array();
    foreach($this->_final_prices as $key => $arr) {
      foreach($arr as $a) {
        if(is_numeric($a)) {
          $ctr++;
        }
      }
      if($ctr == count($this->_wanted_items)) {
        $final_array[$key] = $arr;
        $ctr = 0;
      } else {
        $ctr = 0;
      }
    }
    return $final_array;
  }

  // processes combo item and pushes it onto final_prices array to be used later in determining cheapest item
  private function processComboItems() {
    $this->changeIndividualArrayFormat();
    if(count($this->_combo_items) != 0) {
      $final_combo_list = array();
      // check array for all wanted items
      foreach($this->_combo_items as $item) {
        foreach($this->_wanted_items as $wanted) {
          foreach($item['items'] as $individual) {
            if($individual == $wanted) {
              $final_combo_list[] = true;
            }
          }
        }
        if(count($this->_wanted_items) == count($final_combo_list)) {
          array_push($this->_final_prices, array($item['id'], $item['price']));
        }
      }
    } else {
      return true;
    }
  }

  // changing the array format of the final prices before adding the combo items. This was done so combo items would not overwrite the key of the individual items in array if they came from the same restaurant
  private function changeIndividualArrayFormat() {
    $temp = array();
    foreach($this->_final_prices as $key => $value) {
      $temp[] = array($key, $value);
    }
    $this->_final_prices = $temp;
  }


  // determines cheapest menu/restaurant
  // IF ITEMS ARE THE SAME PRICE IT WILL DISPLAY THE FIRST OPTION 
  // (SO THE INDIVIDUAL ITEMS ADDED UP, BUT WONT BE ABLE TO TELL DIFFERECE BECAUSE THE TOTAL OF THOSE TWO AND RESTAURANT KEY ARE THE ONLY THINGS DISPLAYED)
  //    WOULD HAVE DONE SOMETHING TO OUTPUT BOTH IF I HAD TIME
  private function determineCheapestPlace() {
    $min = PHP_INT_MAX;
    $display = array();
    foreach($this->_final_prices as $items) {
      if($items[1] < $min) {
        $min = $items[1];
        $display = array($items[0], $min);
      }
    }
    if(count($display) != 0) {
      echo implode(', ', $display);
    } else {
      echo 'nil  (as there is no single restaurant that satisfies your desires)'; 
    }
  }
}