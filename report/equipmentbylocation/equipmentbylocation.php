<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

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

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Nelly Mahu-Lasson
 *
 * Purpose of file:
 *       Generate location report
 *       Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

$report->setColumns(array(new PluginReportsColumn('entity', $LANG['entity'][0]),
                          new PluginReportsColumn('location', $LANG['common'][15]),
                          new PluginReportsColumnInteger('computernumber',$LANG['Menu'][0]),
                          new PluginReportsColumnInteger('networknumber', $LANG['Menu'][1]),
                          new PluginReportsColumnInteger('monitornumber', $LANG['Menu'][3]),
                          new PluginReportsColumnInteger('printernumber', $LANG['Menu'][2]),
                          new PluginReportsColumnInteger('peripheralnumber', $LANG['Menu'][16]),
                          new PluginReportsColumnInteger('phonenumber', $LANG['Menu'][34])));

$query = "SELECT i.`entity`, i.`location`, i.`computernumber`, i.`networknumber`,
                 i.`monitornumber`, i.`printernumber`, j.`peripheralnumber`, l.`phonenumber`
          FROM (SELECT g.`entity`, g.`location`, g.`computernumber`, g.`networknumber`,
                       g.`monitornumber`, h.`printernumber`, g.`id`
                FROM (SELECT e.`entity`, e.`location`, e.`computernumber`, e.`networknumber`,
                             f.`monitornumber`, e.`id`
                      FROM (SELECT c.`entity`, c.`location`, c.`computernumber`, d.`networknumber`,
                                   c.`id`
                            FROM (SELECT a.`entity`, a.`location`, b.`computernumber`, a.`id`
                                  FROM (SELECT `glpi_entities`.`completename` AS entity,
                                               `glpi_locations`.`completename` AS location,
                                               `glpi_locations`.`id` AS id
                                        FROM `glpi_locations`
                                        LEFT JOIN `glpi_entities`
                                          ON (`glpi_locations`.`entities_id`=`glpi_entities`.`id`) ".
                                        getEntitiesRestrictRequest(" WHERE ", "glpi_locations").") a
                                  LEFT OUTER JOIN (SELECT count(*) AS computernumber,
                                                          `glpi_computers`.`locations_id` AS id
                                                   FROM `glpi_computers`
                                                   WHERE is_deleted=0 AND is_template=0
                                                   ".getEntitiesRestrictRequest(" AND ", "glpi_computers")."
                                                   GROUP BY `glpi_computers`.`locations_id`) b
                                       ON (a.id = b.id)
                                 ) c
                            LEFT OUTER JOIN (SELECT count(*) AS networknumber,
                                                    `glpi_networkequipments`.`locations_id` AS id
                                             FROM `glpi_networkequipments`
                                             WHERE is_deleted=0 AND is_template=0
                                             ".getEntitiesRestrictRequest(" AND ", "glpi_networkequipments")."
                                             GROUP BY `glpi_networkequipments`.`locations_id`) d
                                 ON (c.id = d.id)
                           ) e
                      LEFT OUTER JOIN (SELECT count(*) AS monitornumber,
                                              `glpi_monitors`.`locations_id` AS id
                                       FROM `glpi_monitors`
                                       WHERE is_deleted=0 AND is_template=0
                                       ".getEntitiesRestrictRequest(" AND ", "glpi_monitors")."
                                       GROUP BY `glpi_monitors`.`locations_id`) f
                           ON (e.id = f.id)
                     ) g
                LEFT OUTER JOIN (SELECT count(*) AS printernumber,
                                        `glpi_printers`.`locations_id` AS id
                                 FROM `glpi_printers`
                                 WHERE is_deleted=0 AND is_template=0
                                 ".getEntitiesRestrictRequest(" AND ", "glpi_printers")."
                                 GROUP BY `glpi_printers`.`locations_id`) h
                     ON (g.id = h.id)
               ) i
          LEFT OUTER JOIN (SELECT count(*) AS peripheralnumber,
                                  `glpi_peripherals`.`locations_id` AS id
                              FROM `glpi_peripherals`
                              WHERE is_deleted=0 AND is_template=0
                              ".getEntitiesRestrictRequest(" AND ", "glpi_peripherals")."
                              GROUP BY `glpi_peripherals`.`locations_id`) j
               ON (i.id = j.id)
          LEFT OUTER JOIN (SELECT count(*) AS phonenumber,
                                  `glpi_phones`.`locations_id` AS id
                           FROM `glpi_phones`
                           WHERE is_deleted=0 AND is_template=0
                           ".getEntitiesRestrictRequest(" AND ", "glpi_phones")."
                           GROUP BY `glpi_phones`.`locations_id`) l
               ON (i.id = l.id)
          ORDER BY i.entity, i.location";

$report->setGroupBy("entity");
$report->setSqlRequest($query);
$report->execute();
?>