<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

$report = new PluginReportsAutoReport(__('licensesexpire_report_title'));

$report->setColumns(array('expire'       => __('Valid to', 'reports'),
                          'name'         => __('License name'),
                          'software'     => sprintf(__('%1$s - %2$s'),
                                                    _n('Software', 'Software', 1),
                                                     __('Purchase version')),
                          'serial'       => __('Serial number'),
                          'completename' => __('Entity'),
                          'comments'     => __('Comments'),
                          'ordinateur'   => __('Computer')));

$query = "SELECT `glpi_softwarelicenses`.`expire`,
                 `glpi_softwarelicenses`.`name`,
                 CONCAT(`glpi_softwares`.`name`,' - ',buyversion.`name`) AS software,
                 `glpi_softwarelicenses`.`serial`,
                 `glpi_entities`.`completename`,
                 `glpi_softwarelicenses`.`comment`,
                 `glpi_computers`.`name` AS ordinateur
          FROM `glpi_softwarelicenses`
          LEFT JOIN `glpi_softwares`
               ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
          LEFT JOIN `glpi_softwarelicensetypes`
            ON (`glpi_softwarelicensetypes`.`id`=`glpi_softwarelicenses`.`softwarelicensetypes_id`)
          LEFT JOIN `glpi_softwareversions` AS buyversion
               ON (buyversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_buy`)
          LEFT JOIN `glpi_entities`
               ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
          LEFT JOIN `glpi_computers_softwarelicenses`
               ON (`glpi_softwarelicenses`.`id` = `glpi_computers_softwarelicenses`.`softwarelicenses_id`)
          LEFT JOIN `glpi_computers`
               ON (`glpi_computers`.`id` = `glpi_computers_softwarelicenses`.`computers_id`)
          WHERE `glpi_softwares`.`is_deleted` = '0'
                AND `glpi_softwares`.`is_template` = '0' " .
                getEntitiesRestrictRequest(' AND ', 'glpi_softwarelicenses') ."
          ORDER BY `glpi_softwarelicenses`.`expire`, `name`";

$report->setGroupBy(array('expire',
                          'name'));
$report->setSqlRequest($query);
$report->execute();
?>