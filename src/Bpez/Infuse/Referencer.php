<?php  namespace Bpez\Infuse;

  /**
    * @link
    * http://www.wardt.info/nested-objects-arrays-and-variable-variables-in-php
    *
    * @copyright   Copyright (C) 2013 Richard Wardt van Duijvenbode
    * @license     GPLv3 - https://www.gnu.org/licenses/gpl-3.0.html
    *
    *     This program is free software: you can redistribute it and/or modify
    *     it under the terms of the GNU General Public License as published by
    *     the Free Software Foundation, either version 3 of the License, or
    *     (at your option) any later version.
    *
    *     This program is distributed in the hope that it will be useful,
    *     but WITHOUT ANY WARRANTY; without even the implied warranty of
    *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    *     GNU General Public License for more details.
    *
    *     You should have received a copy of the GNU General Public License
    *     along with this program. If not, see <http://www.gnu.org/licenses/>.
    *
    * File Name:   Referencer.php
    * Description: PHP class for referencing nested variables.
    *
    */
 
  class Referencer {
 
    /**
      * Usage:
      *
      *   Common:
      *
      *     $reference =& Referencer::getReference(
      *         "$variables.config->call['cu']");
      *
      *   Using the reference parameter, it is possible to give a starting
      *   reference:
      *
      *     $reference =& Referencer::getReference(
      *         "config->call['cu']",
      *         $variables);
      */
    public static function &getReference(
        $name,
        &$reference = NULL) {
 
      // Remove unused name data, unify the rest, split it up in variables,
      // and remove the empty items.
      $variables = preg_replace(
          '/\$|->|\[|\'|"|\]|::/',
          '.',
          $name);
      $variables = explode(
          '.',
          $variables);
      $variables = array_filter($variables);
 
      foreach($variables as $variable)
 
        // Check if there is a reference point to start with.
        if(isset($reference))
 
          // Reference is an object. Non-existent attributes will be created.
          if(is_object($reference))
            $reference =& $reference->$variable;
 
          // Reference is probably an array. Non-existent items will be
          // created.
          else
            $reference =& $reference[$variable];
 
         // First variable. The $GLOBALS variable is used to access the
         // target variable.
        else
          $reference =& $GLOBALS[$variable];
 
      return $reference;
 
    } // Closing bracket of the getReference method.
 
    /**
      * Usage:
      *
      *   Referencer::setValue(
      *       '$variables->response',
      *       'Hello World!');
      *
      *   Ommiting the value, sets it to NULL.
      *
      *   The reference parameter works the same as it does for the
      *   getReference method.
      */
    public static function setValue(
        $name,
        $value = NULL,
        &$reference = NULL) {
      $reference =& Referencer::getReference(
          $name,
          $reference);
      $reference = $value;
    }
 
    /**
      * Usage:
      *
      *   echo Referencer::getValue("variables.config->call");
      *
      *   The reference parameter works the same as it does for the
      *   getReference method.
      */
    public static function getValue(
        $name,
        &$reference = NULL) {
      return
          Referencer::getReference(
              $name,
              $reference);
    }
 
  } // Closing bracket of the Referencer class.
 
?>