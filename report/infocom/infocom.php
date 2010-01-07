<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Remi Collet
 * 
 * Purpose of file: 
 * 		Big UNION to have a report including all inventory
 * 
 * ----------------------------------------------------------------------
 */ 

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 


/*
 * TODO : add a form to choose selection criteria
 * 
 * - num_immo not empry
 * - otherserial not empty
 * - etc
 * 
 */

$report = new PluginReportsAutoReport();

$report->setColumnsNames(array('nature'            => $LANG["state"][6],
                               'manufacturer'      => $LANG["common"][5],
                               'type'              => $LANG["common"][17],
                               'model'             => $LANG["common"][22],
                               'name'              => $LANG["common"][16],
                               'serial'            => $LANG["common"][19],
                               'otherserial'       => $LANG["common"][20],
                               'location'          => $LANG["common"][15],
                               'building'          => $LANG["setup"][99],
                               'room'              => $LANG["setup"][100],
                               'groupe'            => $LANG["common"][35],
                               'state'             => $LANG["joblist"][0],
                               'immo_number'       => $LANG["financial"][20],
                               'buy_date'          => $LANG["financial"][14],
                               'use_date'          => $LANG["financial"][76],
                               'warranty_duration' => $LANG["financial"][15],
                               'warranty_info'     => $LANG["financial"][16],
                               'supplier'          => $LANG["financial"][26],
                               'order_number'      => $LANG["financial"][18],
                               'delivery_number'   => $LANG["financial"][19],
                               'value'             => $LANG["financial"][21],
                               'warranty_value'    => $LANG["financial"][78],
                               'sink_time'         => $LANG["financial"][23],
                               'sink_type'         => $LANG["financial"][22],
                               'sink_coeff'        => $LANG["financial"][77],
                               'bill'              => $LANG["financial"][82],
                               'budget'            => $LANG["financial"][87]),

$sql = "(SELECT '" . $LANG["software"][11] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_softwarelicensetypes`.`name` AS type, 
                CONCAT(glpi_softwares.name,' ',buyversion.name) AS model, 
                `glpi_softwarelicenses`.`name` AS name, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`,
                                `warranty_info`,`order_number`,`delivery_number`, `warranty_value`,
                                `sink_time`, `sink_type`, `sink_coeff`,`bill`,
                `glpi_suppliers`.`name` AS supplier,
                `glpi_budgets`.`name` AS budget
         FROM `glpi_softwarelicenses`
         LEFT JOIN `glpi_softwares` 
            ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
         LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_softwares`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` 
            ON (`glpi_manufacturers`.`id` = `glpi_softwares`.`manufacturers_id`)
         LEFT JOIN `glpi_softwareversions` 
            ON (`glpi_softwares`.`id` = `glpi_softwareversions`.`softwares_id`) 
         LEFT JOIN `glpi_softwareversions` AS buyversion 
            ON (buyversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_buy`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = buyversion.`states_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_softwares`.`groups_id` = `glpi_groups`.`id`)
         LEFT JOIN `glpi_softwarelicensetypes` 
            ON (`glpi_softwarelicensetypes`.`id` = `glpi_softwarelicenses`.`softwarelicensetypes_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_softwarelicenses`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_softwares`.`is_deleted` = '0' 
               AND `glpi_softwares`.`is_template` = '0'
               AND `glpi_infocoms`.`itemtype` = 'SoftwareLicense'
               AND (`glpi_softwarelicenses`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_softwares") .") 

         UNION (
         SELECT '" . $LANG["help"][25] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_computertypes`.`name` AS type,
                `glpi_computermodels`.`name` AS model,
                `glpi_computers`.`name` AS name, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`, 
                                `warranty_info`, `order_number`, `delivery_number`, `warranty_value`, 
                                `sink_time`, `sink_type`, `sink_coeff`, `bill`, 
             `glpi_suppliers`.`name` AS supplier, 
             `glpi_budgets`.`name` AS budget
         FROM `glpi_computers`
         LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_computers`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` 
               ON (`glpi_manufacturers`.`id` = `glpi_computers`.`manufacturers_id`)
         LEFT JOIN `glpi_computermodels` 
               ON (`glpi_computermodels`.`id` = `glpi_computers`.`computermodels_id`)
         LEFT JOIN `glpi_computertypes` 
               ON (`glpi_computertypes`.`id` = `glpi_computers`.`computertypes_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id` = `glpi_computers`.`groups_id`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = `glpi_computers`.`states_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_computers`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_computers`.`is_deleted` = '0'
               AND `glpi_computers`.`is_template` = '0'
               AND `glpi_infocoms`.`itemtype` = 'Computer'
               AND (`glpi_computers`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_computers") .") 
 
        UNION (
         SELECT '" . $LANG["help"][28] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_monitortypes`.`name` AS type,
                `glpi_monitormodels`.`name` AS model,
                `glpi_monitors`.`name`, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`,
                                `warranty_info`,`order_number`,`delivery_number`, `warranty_value`,
                                `sink_time`, `sink_type`, `sink_coeff`,`bill`,
                `glpi_suppliers`.`name` AS supplier,
                `glpi_budgets`.`name` AS budget
         FROM `glpi_monitors`
         LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_monitors`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` 
               ON (`glpi_manufacturers`.`id` = `glpi_monitors`.`manufacturers_id`)
         LEFT JOIN `glpi_monitormodels` 
               ON (`glpi_monitormodels`.`id` = `glpi_monitors`.`monitormodels_id`)
         LEFT JOIN `glpi_monitortypes` 
               ON (`glpi_monitortypes`.`id` = `glpi_monitors`.`monitortypes_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id` = `glpi_monitors`.`groups_id`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = `glpi_monitors`.`states_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_monitors`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_monitors`.`is_deleted` = '0'
               AND `glpi_monitors`.`is_template` = '0'
               AND `glpi_infocoms`.`itemtype` = 'Monitor'
               AND (`glpi_monitors`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_monitors") .") 

         UNION (
         SELECT '" . $LANG["help"][27] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_printertypes`.`name` AS type,
                `glpi_printermodels`.`name` AS model,
                `glpi_printers`.`name`, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`,
                                `warranty_info`,`order_number`,`delivery_number`, `warranty_value`,
                                `sink_time`, `sink_type`, `sink_coeff`,`bill`,
                `glpi_suppliers`.`name` AS supplier,
                `glpi_budgets`.`name` AS budget
         FROM `glpi_printers`
         LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_printers`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` 
               ON (`glpi_manufacturers`.`id` = `glpi_printers`.`manufacturers_id`)
         LEFT JOIN `glpi_printermodels` 
               ON (`glpi_printermodels`.`id` = `glpi_printers`.`printermodels_id`)
         LEFT JOIN `glpi_printertypes` 
               ON (`glpi_printertypes`.`id` = `glpi_printers`.`printertypes_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id` = `glpi_printers`.`groups_id`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = `glpi_printers`.`states_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_printers`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_printers`.`is_deleted` = '0' 
               AND `glpi_infocoms`.`itemtype` = 'Printer'
               AND (`glpi_printers`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_printers") .") 

         UNION (
         SELECT '" . $LANG["help"][26] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_networkequipmenttypes`.`name` AS type,
                `glpi_networkequipmentmodels`.`name` AS model,
                `glpi_networkequipments`.`name`, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`,
                                `warranty_info`,`order_number`,`delivery_number`, `warranty_value`,
                                `sink_time`, `sink_type`, `sink_coeff`,`bill`,
                `glpi_suppliers`.`name` AS supplier,
                `glpi_budgets`.`name` AS budget
         FROM `glpi_networkequipments`
         LEFT JOIN `glpi_locations` 
            ON (`glpi_locations`.`id` = `glpi_networkequipments`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` 
            ON (`glpi_manufacturers`.`id` = `glpi_networkequipments`.`manufacturers_id`)
         LEFT JOIN `glpi_networkequipmentmodels` 
            ON (`glpi_networkequipmentmodels`.`id` = `glpi_networkequipments`.`networkequipmentmodels_id`)
         LEFT JOIN `glpi_networkequipmenttypes` 
            ON (`glpi_networkequipmenttypes`.`id` = `glpi_networkequipments`.`networkequipmenttypes_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id` = `glpi_networkequipments`.`groups_id`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = `glpi_networkequipments`.`states_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_networkequipments`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_networkequipments`.`is_deleted` = '0'
               AND `glpi_networkequipments`.`is_template` = '0'
               AND `glpi_infocoms`.`itemtype` = 'NetworkEquipment'
               AND (`glpi_networkequipments`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_networkequipments") .") 

         UNION (
         SELECT '" . $LANG["help"][29] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_peripheraltypes`.`name` AS type,
                `glpi_peripheralmodels`.`name` AS model,
                `glpi_peripherals`.`name`, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`,
                                `warranty_info`,`order_number`,`delivery_number`, `warranty_value`,
                                `sink_time`, `sink_type`, `sink_coeff`,`bill`,
                `glpi_suppliers`.`name` AS supplier,
                `glpi_budgets`.`name` AS budget
         FROM `glpi_peripherals`
         LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_peripherals`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` 
               ON (`glpi_manufacturers`.`id` = `glpi_peripherals`.`manufacturers_id`)
         LEFT JOIN `glpi_peripheralmodels` 
               ON (`glpi_peripheralmodels`.`id` = `glpi_peripherals`.`peripheralmodels_id`)
         LEFT JOIN `glpi_peripheraltypes` 
               ON (`glpi_peripheraltypes`.`id` = `glpi_peripherals`.`peripheraltypes_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id` = `glpi_peripherals`.`groups_id`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = `glpi_peripherals`.`states_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_peripherals`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_peripherals`.`is_deleted` = '0' 
               AND `glpi_peripherals`.`is_template` = '0'
               AND `glpi_infocoms`.`itemtype` = 'Peripheral'
               AND (`glpi_peripherals`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_peripherals") .") 

         UNION (
         SELECT '" . $LANG["help"][35] . "' AS nature, 
                `glpi_manufacturers`.`name` AS manufacturer,
                `glpi_phonetypes`.`name` AS type,
                `glpi_phonemodels`.`name` AS model,
                `glpi_phones`.`name`, `serial`, `otherserial`, 
                `glpi_locations`.`completename` AS location, `building`, `room`,
                `glpi_groups`.`name` AS groupe,
                `glpi_states`.`name` AS state,
                `glpi_infocoms`.`value`, `immo_number`, `buy_date`, `use_date`, `warranty_duration`,
                                `warranty_info`,`order_number`,`delivery_number`, `warranty_value`,
                                `sink_time`, `sink_type`, `sink_coeff`,`bill`,
                `glpi_suppliers`.`name` AS supplier,
                `glpi_budgets`.`name` AS budget
         FROM `glpi_phones`
         LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id` = `glpi_phones`.`locations_id`)
         LEFT JOIN `glpi_manufacturers` ON (`glpi_manufacturers`.`id` = `glpi_phones`.`manufacturers_id`)
         LEFT JOIN `glpi_phonemodels` ON (`glpi_phonemodels`.`id` = `glpi_phones`.`phonemodels_id`)
         LEFT JOIN `glpi_phonetypes` ON (`glpi_phonetypes`.`id` = `glpi_phones`.`phonetypes_id`)
         LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id` = `glpi_phones`.`groups_id`)
         LEFT JOIN `glpi_states` ON (`glpi_states`.`id` = `glpi_phones`.`states_id`)
         LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`items_id` = `glpi_phones`.`id`)
         LEFT JOIN `glpi_suppliers` ON (`glpi_suppliers`.`id` = `glpi_infocoms`.`suppliers_id`)
         LEFT JOIN `glpi_budgets` ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
         WHERE `glpi_phones`.`is_deleted` = '0' 
               AND `glpi_phones`.`is_template` = '0'
               AND `glpi_infocoms`.`itemtype` = 'Phone'
               AND (`glpi_phones`.`otherserial` != '' 
                    OR `glpi_infocoms`.`immo_number` !='')" .
               getEntitiesRestrictRequest(" AND ", "glpi_phones") .")");

$report->setGroupBy('entity');
$report->setSqlRequest($sql);
$report->execute();

?>