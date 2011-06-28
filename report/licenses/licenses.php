<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Nelly Lasson
 *
 * Purpose of file:
 *    Generate a detailed license report
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

$license = new PluginReportsSoftwareWithLicenseCriteria($report);

$license->setSqlField("`glpi_softwarelicenses`.`softwares_id`");

$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()
    && $license->getParameterValue() >0) {

   $report->setSubNameAuto();

   $report->setColumnsNames(array ("license" => $LANG['software'][11],
                                   "serial"  => $LANG['common'][19],
                                   "nombre"  => $LANG['tracking'][29],
                                   "type"    => $LANG['common'][17],
                                   "buy"     => $LANG['plugin_reports']['licenses'][2],
                                   "used"    => $LANG['plugin_reports']['licenses'][3],
                                   "expire"  => $LANG['financial'][98],
                                   "comment" => $LANG['common'][25],
                                   "name"    => $LANG['help'][25]));

   $query = "SELECT `glpi_softwarelicenses`.`name` AS license,
                    `glpi_softwarelicenses`.`serial`,
                    `glpi_softwarelicenses`.`number` AS nombre,
                    `glpi_softwarelicensetypes`.`name` AS type,
                    buyversion.`name` AS buy,
                    useversion.`name` AS used,
                    `glpi_softwarelicenses`.`expire`,
                    `glpi_softwarelicenses`.`comment`,
                    `glpi_computers`.`name`
             FROM `glpi_softwarelicenses`
             LEFT JOIN `glpi_softwares`
                  ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
             LEFT JOIN `glpi_computers_softwarelicenses`
                  ON (`glpi_softwarelicenses`.`id` = `glpi_computers_softwarelicenses`.`softwarelicenses_id`)
             LEFT JOIN `glpi_computers`
                  ON (`glpi_computers`.`id` = `glpi_computers_softwarelicenses`.`computers_id`)
             LEFT JOIN `glpi_softwareversions` AS buyversion
                  ON (buyversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_buy`)
             LEFT JOIN `glpi_softwareversions` AS useversion
                  ON (useversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_use`)
             LEFT JOIN `glpi_softwarelicensetypes`
                  ON (`glpi_softwarelicensetypes`.`id`=`glpi_softwarelicenses`.`softwarelicensetypes_id`)
             LEFT JOIN `glpi_entities`
                  ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)".
             $report->addSqlCriteriasRestriction("WHERE")."
                   AND `glpi_softwares`.`is_deleted` = '0'
                   AND `glpi_softwares`.`is_template` = '0' " .
                   getEntitiesRestrictRequest(' AND ', 'glpi_softwares') ."
             ORDER BY license";

   $report->setGroupBy("license");
   $report->setSqlRequest($query);
   $report->execute();

}

?>