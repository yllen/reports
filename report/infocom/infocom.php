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

$NEEDED_ITEMS=array("search");
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

$sql = 
"(
SELECT '" . $LANG["software"][11] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_dropdown_licensetypes.name AS type, 
	CONCAT(glpi_software.name,' ',buyversion.name) AS model, 
	glpi_softwarelicenses.name AS name,
	glpi_softwarelicenses.serial AS serial, 
	glpi_softwarelicenses.otherserial AS otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	glpi_infocoms.num_immo AS num_immo, 
	glpi_infocoms.buy_date, 
	glpi_infocoms.use_date, 
	glpi_infocoms.warranty_duration AS warranty_duration,
	glpi_infocoms.warranty_info AS warranty_info,
	glpi_enterprises.name AS supplier,
	glpi_infocoms.num_commande AS num_commande,
	glpi_infocoms.bon_livraison AS bon_livraison,
	glpi_infocoms.value AS value, 
	glpi_infocoms.warranty_value AS warranty_value,
	glpi_infocoms.amort_time AS amort_time, 
	glpi_infocoms.amort_type AS amort_type, 
	glpi_infocoms.amort_coeff AS amort_coeff,
	glpi_infocoms.facture AS facture,
	glpi_dropdown_budget.name AS budget
FROM glpi_softwarelicenses
	LEFT JOIN glpi_software ON (glpi_softwarelicenses.sID=glpi_software.ID)
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=glpi_software.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=glpi_software.FK_glpi_enterprise)
	LEFT JOIN glpi_softwareversions ON (glpi_software.ID=glpi_softwareversions.sID) 
	LEFT JOIN glpi_softwareversions AS buyversion ON (buyversion.ID=glpi_softwarelicenses.buy_version)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=buyversion.state)
	LEFT JOIN glpi_groups ON (glpi_software.FK_groups=glpi_groups.ID)
	LEFT JOIN glpi_dropdown_licensetypes ON (glpi_dropdown_licensetypes.ID=glpi_softwarelicenses.type)
	LEFT JOIN glpi_infocoms ON (glpi_infocoms.FK_device=glpi_softwarelicenses.ID)
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=glpi_infocoms.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=glpi_infocoms.budget)
WHERE glpi_software.deleted=0 
	AND glpi_software.is_template='0'
	AND glpi_infocoms.device_type=" . SOFTWARELICENSE_TYPE . "
	AND (glpi_softwarelicenses.otherserial!='' OR glpi_infocoms.num_immo !='')" .
getEntitiesRestrictRequest(" AND ", "glpi_software") .
") UNION (
SELECT '" . $LANG["help"][25] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_type_computers.name AS type,
	glpi_dropdown_model.name AS model,
	dev.name, serial, otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	num_immo, buy_date, use_date, warranty_duration, warranty_info,
	glpi_enterprises.name AS supplier, num_commande, bon_livraison, value, warranty_value, amort_time, amort_type, amort_coeff, facture, glpi_dropdown_budget.name AS budget
FROM glpi_computers dev
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=dev.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=dev.FK_glpi_enterprise)
	LEFT JOIN glpi_dropdown_model ON (glpi_dropdown_model.ID=dev.model)
	LEFT JOIN glpi_type_computers ON (glpi_type_computers.ID=dev.type)
	LEFT JOIN glpi_groups ON (glpi_groups.ID=dev.FK_groups)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=dev.state)
, glpi_infocoms inf
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=inf.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=inf.budget)
WHERE dev.deleted=0
	AND inf.FK_device=dev.ID AND inf.device_type=" . COMPUTER_TYPE . "
	AND (dev.otherserial!='' OR inf.num_immo !='')" . 
getEntitiesRestrictRequest(" AND ", "dev") .
") UNION (
SELECT '" . $LANG["help"][28] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_type_monitors.name AS type,
	glpi_dropdown_model_monitors.name AS model,
	dev.name, serial, otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	num_immo, buy_date, use_date, warranty_duration, warranty_info,
	glpi_enterprises.name AS supplier, num_commande, bon_livraison, value, warranty_value, amort_time, amort_type, amort_coeff, facture, glpi_dropdown_budget.name AS budget
FROM glpi_monitors dev
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=dev.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=dev.FK_glpi_enterprise)
	LEFT JOIN glpi_dropdown_model_monitors ON (glpi_dropdown_model_monitors.ID=dev.model)
	LEFT JOIN glpi_type_monitors ON (glpi_type_monitors.ID=dev.type)
	LEFT JOIN glpi_groups ON (glpi_groups.ID=dev.FK_groups)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=dev.state)
, glpi_infocoms inf
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=inf.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=inf.budget)
WHERE dev.deleted=0
	AND inf.FK_device=dev.ID AND inf.device_type=" . MONITOR_TYPE . "
	AND (dev.otherserial!='' OR inf.num_immo !='')" . 
getEntitiesRestrictRequest(" AND ", "dev") .
") UNION (
SELECT '" . $LANG["help"][27] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_type_printers.name AS type,
	glpi_dropdown_model_printers.name AS model,
	dev.name, serial, otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	num_immo, buy_date, use_date, warranty_duration, warranty_info,
	glpi_enterprises.name AS supplier, num_commande, bon_livraison, value, warranty_value, amort_time, amort_type, amort_coeff, facture, glpi_dropdown_budget.name AS budget
FROM glpi_printers dev
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=dev.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=dev.FK_glpi_enterprise)
	LEFT JOIN glpi_dropdown_model_printers ON (glpi_dropdown_model_printers.ID=dev.model)
	LEFT JOIN glpi_type_printers ON (glpi_type_printers.ID=dev.type)
	LEFT JOIN glpi_groups ON (glpi_groups.ID=dev.FK_groups)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=dev.state)
, glpi_infocoms inf
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=inf.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=inf.budget)
WHERE dev.deleted=0
	AND inf.FK_device=dev.ID AND inf.device_type=" . PRINTER_TYPE . "
	AND (dev.otherserial!='' OR inf.num_immo !='')" . 
getEntitiesRestrictRequest(" AND ", "dev") .
") UNION (
SELECT '" . $LANG["help"][26] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_type_networking.name AS type,
	glpi_dropdown_model_networking.name AS model,
	dev.name, serial, otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	num_immo, buy_date, use_date, warranty_duration, warranty_info,
	glpi_enterprises.name AS supplier, num_commande, bon_livraison, value, warranty_value, amort_time, amort_type, amort_coeff, facture, glpi_dropdown_budget.name AS budget
FROM glpi_networking dev
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=dev.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=dev.FK_glpi_enterprise)
	LEFT JOIN glpi_dropdown_model_networking ON (glpi_dropdown_model_networking.ID=dev.model)
	LEFT JOIN glpi_type_networking ON (glpi_type_networking.ID=dev.type)
	LEFT JOIN glpi_groups ON (glpi_groups.ID=dev.FK_groups)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=dev.state)
, glpi_infocoms inf
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=inf.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=inf.budget)
WHERE dev.deleted=0
	AND inf.FK_device=dev.ID AND inf.device_type=" . NETWORKING_TYPE . "
	AND (dev.otherserial!='' OR inf.num_immo !='')" . 
getEntitiesRestrictRequest(" AND ", "dev") .
") UNION (
SELECT '" . $LANG["help"][29] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_type_peripherals.name AS type,
	glpi_dropdown_model_peripherals.name AS model,
	dev.name, serial, otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	num_immo, buy_date, use_date, warranty_duration, warranty_info,
	glpi_enterprises.name AS supplier, num_commande, bon_livraison, value, warranty_value, amort_time, amort_type, amort_coeff, facture, glpi_dropdown_budget.name AS budget
FROM glpi_peripherals dev
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=dev.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=dev.FK_glpi_enterprise)
	LEFT JOIN glpi_dropdown_model_peripherals ON (glpi_dropdown_model_peripherals.ID=dev.model)
	LEFT JOIN glpi_type_peripherals ON (glpi_type_peripherals.ID=dev.type)
	LEFT JOIN glpi_groups ON (glpi_groups.ID=dev.FK_groups)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=dev.state)
, glpi_infocoms inf
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=inf.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=inf.budget)
WHERE dev.deleted=0
	AND inf.FK_device=dev.ID AND inf.device_type=" . PERIPHERAL_TYPE . "
	AND (dev.otherserial!='' OR inf.num_immo !='')" . 
getEntitiesRestrictRequest(" AND ", "dev") .
") UNION (
SELECT '" . $LANG["help"][35] . "' AS nature, 
	glpi_dropdown_manufacturer.name AS manufacturer,
	glpi_type_phones.name AS type,
	glpi_dropdown_model_phones.name AS model,
	dev.name, serial, otherserial, 
	glpi_dropdown_locations.completename AS location,
	glpi_groups.name AS groupe,
	glpi_dropdown_state.name AS state,
	num_immo, buy_date, use_date, warranty_duration, warranty_info,
	glpi_enterprises.name AS supplier, num_commande, bon_livraison, value, warranty_value, amort_time, amort_type, amort_coeff, facture, glpi_dropdown_budget.name AS budget
FROM glpi_phones dev
	LEFT JOIN glpi_dropdown_locations ON (glpi_dropdown_locations.ID=dev.location)
	LEFT JOIN glpi_dropdown_manufacturer ON (glpi_dropdown_manufacturer.ID=dev.FK_glpi_enterprise)
	LEFT JOIN glpi_dropdown_model_phones ON (glpi_dropdown_model_phones.ID=dev.model)
	LEFT JOIN glpi_type_phones ON (glpi_type_phones.ID=dev.type)
	LEFT JOIN glpi_groups ON (glpi_groups.ID=dev.FK_groups)
	LEFT JOIN glpi_dropdown_state ON (glpi_dropdown_state.ID=dev.state)
, glpi_infocoms inf
	LEFT JOIN glpi_enterprises ON (glpi_enterprises.ID=inf.FK_enterprise)
	LEFT JOIN glpi_dropdown_budget ON (glpi_dropdown_budget.ID=inf.budget)
WHERE dev.deleted=0
	AND inf.FK_device=dev.ID AND inf.device_type=" . PHONE_TYPE . "
	AND (dev.otherserial!='' OR inf.num_immo !='')" . 
getEntitiesRestrictRequest(" AND ", "dev") .
")";

simpleReport(
	// Report Name
	"infocom", 

	// SQL statement
	$sql,
	
	// Columns title (optional), from $LANG
	array (
		"nature" => $LANG["state"][6],
		"manufacturer" => $LANG["common"][5],
		"type" => 	$LANG["common"][17],
		"model" => 	$LANG["common"][22],
		"name" => 	$LANG["common"][16],
		"serial" => $LANG["common"][19],
		"otherserial" => $LANG["common"][20],
		"location" => $LANG["common"][15],
		"groupe" => $LANG["common"][35],
		"state" => 	$LANG["joblist"][0],
		"num_immo" => $LANG["financial"][20],
		"buy_date" => $LANG["financial"][14],
		"use_date" => $LANG["financial"][76],
		"warranty_duration" => $LANG["financial"][15],
		"warranty_info" => $LANG["financial"][16],
		"supplier" => $LANG["financial"][26],
		"num_commande" => $LANG["financial"][18],
		"bon_livraison" => $LANG["financial"][19],
		"value" => $LANG["financial"][21],
		"warranty_value" => $LANG["financial"][78],
		"amort_time" => $LANG["financial"][23],
		"amort_type" => $LANG["financial"][22],
		"amort_coeff" => $LANG["financial"][77],
		"facture" => $LANG["financial"][82],
		"budget" => $LANG["financial"][87]
		),
		
	// Sub title
	"",
	
	// Group by
	array ("entity")
	);
 
?>