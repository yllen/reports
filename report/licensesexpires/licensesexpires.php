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
 * 		Generate a detailed license report
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new AutoReport();

$report->setColumnsNames(array('expire'       => $LANG['financial'][88],
                               'name'         => $LANG['plugin_reports']['licensesexpires'][2],
                               'software'     => $LANG['plugin_reports']['licensesexpires'][3],
                               'serial'       => $LANG['common'][19],
                               'completename' => $LANG['entity'][0],
                               'comments'     => $LANG['common'][25],
                               'ordinateur'   => $LANG['help'][25]));

$query = "SELECT `glpi_softwareslicenses`.`expire`,
                 `glpi_softwareslicenses`.`name`,
                 CONCAT(`glpi_softwares`.`name`,' - ',buyversion.`name`) AS software,
                 `glpi_softwareslicenses`.`serial`,
                 `glpi_entities`.`completename`,
                 `glpi_softwareslicenses`.`comment`,
                 `glpi_computers`.`name` AS ordinateur
          FROM `glpi_softwareslicenses`
          LEFT JOIN `glpi_softwares` 
               ON (`glpi_softwareslicenses`.`softwares_id` = `glpi_softwares`.`id`)
          LEFT JOIN `glpi_softwareslicensestypes` 
            ON (`glpi_softwareslicensestypes`.`id`=`glpi_softwareslicenses`.`softwareslicensestypes_id`)
          LEFT JOIN `glpi_softwaresversions` AS buyversion 
               ON (buyversion.`id` = `glpi_softwareslicenses`.`softwaresversions_id_buy`)
          LEFT JOIN `glpi_infocoms` 
               ON (`glpi_infocoms`.`items_id` = `glpi_softwareslicenses`.`id`)
          LEFT JOIN `glpi_entities` 
               ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
          LEFT JOIN `glpi_computers` 
               ON (`glpi_softwareslicenses`.`computers_id` = `glpi_computers`.`id`) 
          WHERE `glpi_softwares`.`is_deleted` = '0' 
                AND `glpi_softwares`.`is_template` = '0'
                AND `glpi_infocoms`.`itemtype` = " . SOFTWARELICENSE_TYPE . "
                AND (`glpi_softwareslicenses`.`otherserial` != '' 
                     OR `glpi_infocoms`.`immo_number` !='') " .
                getEntitiesRestrictRequest(' AND ', 'glpi_softwareslicenses') ."
          ORDER BY `glpi_softwareslicenses`.`expire`, `name`";

$report->setGroupBy(array('expire',
                          'name'));
$report->setSqlRequest($query);
$report->execute();

?>