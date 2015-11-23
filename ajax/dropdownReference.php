<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include_once ("../../../inc/includes.php");

Session::checkCentralAccess();

// Make a select box for references
if (isset($_POST["itemtype"])) {
   $query = "SELECT s.`plugin_order_references_id` as id, s.`price_taxfree`, s.`reference_code`, r.`name`
             FROM `glpi_plugin_order_references_suppliers` s
             LEFT JOIN `glpi_plugin_order_references` r 
             ON (s.`plugin_order_references_id` = r.`id`                
               AND  r.`is_active` = 1
               AND  r.`is_deleted` = 0)
             WHERE s.`suppliers_id` = '{$_POST['suppliers_id']}'
             AND r.`itemtype` = '{$_POST['itemtype']}'
             AND r.entities_id = '{$_POST['entities_id']}'
             ORDER BY s.`reference_code`";
   $result = $DB->query($query);
   $number = $DB->numrows($result);

   $values = array(0 => Dropdown::EMPTY_VALUE);
   if ($number) {
      while ($data = $DB->fetch_assoc($result)) {
         $values[$data['id']] = $data['name']." - ".$data['reference_code'];
      }
   }
   Dropdown::showFromArray($_POST['fieldname'], $values, array('rand' => $_POST['rand']));

   $params = array(
      'reference_id' => '__VALUE__',
   );
   Ajax::updateItemOnSelectEvent('dropdown_plugin_order_references_id' . $_POST['rand'],
                                 'show_priceht',
                                 '../ajax/dropdownReference.php',
                                 $params);

// Get price
} elseif (isset($_POST['reference_id'])) {
   $query = "SELECT `price_taxfree`
             FROM `glpi_plugin_order_references_suppliers`
             WHERE `plugin_order_references_id` = '{$_POST['reference_id']}'";
   $result = $DB->query($query);
   $price = $DB->result($result, 0, 'price_taxfree');

   echo '<input type="text" name="price" value="' . str_replace('&nbsp;', ' ', Html::formatNumber($price)) . '" style="text-align:center" size="10" />';
}
