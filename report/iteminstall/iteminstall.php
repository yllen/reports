<?php
/*	----------------------------------------------------------------------
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

//	Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=1;

// Initialization of the variables
define('GLPI_ROOT',  '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport($LANG['plugin_reports']['iteminstall'][1]);

//Report's search criterias
$date = new PluginReportsDateIntervalCriteria($report, 'buy_date');
$type = new PluginReportsItemTypeCriteria($report, 'itemtype', '', 'infocom_types');
$budg = new PluginReportsDropdownCriteria($report, 'budgets_id', 'glpi_budgets', $LANG['financial'][87]);

//Display criterias form is needed
$report->displayCriteriasForm();

$display_type = HTML_OUTPUT;

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();
   $title = $report->getFullTitle();

   $itemtype = $type->getParameterValue();
   if ($itemtype) {
      $types = array($itemtype);
   } else {
      $types = array();
      $sql = "SELECT DISTINCT `itemtype`
              FROM `glpi_infocoms` ".
              getEntitiesRestrictRequest('WHERE', 'glpi_infocoms').
              $date->getSqlCriteriasRestriction('AND').
              $budg->getSqlCriteriasRestriction('AND');
      foreach ($DB->request($sql) as $data) {
         $types[] = $data['itemtype'];
      }
   }

   $result = array();
   foreach ($types as $type) {
      $result[$type] = array();
      // Total of buy equipment
      $crit = "itemtype='$type' ".
              getEntitiesRestrictRequest('AND','glpi_infocoms').
              $budg->getSqlCriteriasRestriction('AND').
              $date->getSqlCriteriasRestriction('AND');

      $result[$type]['buy'] = countElementsInTable('glpi_infocoms', $crit);

      for ($deb=0 ; $deb<12 ; $deb=$fin) {
         $fin = $deb+2;
         $crit2 = $crit;
         if ($deb) {
            $crit2 .= " AND `use_date` >= DATE_ADD(`buy_date`, INTERVAL $deb MONTH) ";
         }
         if ($fin) {
            $crit2 .= " AND `use_date` < DATE_ADD(`buy_date`, INTERVAL $fin MONTH) ";
         }
         $result[$type]["$deb-$fin"] = countElementsInTable('glpi_infocoms', $crit2);
      }
      $crit2 = $crit;
      $crit2 .= " AND (`use_date` IS NULL OR `use_date` >= DATE_ADD(`buy_date`, INTERVAL 12 MONTH))";
      $result[$type]['12+'] = countElementsInTable('glpi_infocoms', $crit2);
   }

   if ($display_type == HTML_OUTPUT) {
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th>$title</th></tr>\n";
         echo "</table></div>\n";
   }
   $nbres = count($result);
   if ($nbres > 0) {
      if ($nbres>1) {
         $nbrows = $nbres*2+2;
         $result['total'] = array();
         reset($result);
         foreach (next($result) as $key => $val) {
            $result['total'][$key] = 0;
         }
      } else {
         $nbrows = 2;
      }
      $nbcols = 9;
      echo Search::showHeader($display_type, $nbrows, $nbcols, true);
      echo Search::showNewLine($display_type);
      $numcol=1;
      echo Search::showHeaderItem($display_type, $LANG['state'][6], $numcol); // itemtype
      echo Search::showHeaderItem($display_type, $LANG['common'][33], $numcol); // total
      echo Search::showHeaderItem($display_type, '0-1', $numcol);
      echo Search::showHeaderItem($display_type, '2-3', $numcol);
      echo Search::showHeaderItem($display_type, '4-5', $numcol);
      echo Search::showHeaderItem($display_type, '6-7', $numcol);
      echo Search::showHeaderItem($display_type, '8-9', $numcol);
      echo Search::showHeaderItem($display_type, '10-11', $numcol);
      echo Search::showHeaderItem($display_type, '12+', $numcol);
      echo Search::showEndLine($display_type);

      $row_num = 1;
      foreach ($result as $itemtype => $row) {
         if ($itemtype == 'total') {
            $name = $LANG['common'][33];
         } else if (class_exists($itemtype)) {
            $item = new $itemtype();
            $name = $item->getTypeName();
         } else {
            continue;
         }

         $numcol=1;
         echo Search::showNewLine($display_type);
         echo Search::showItem($display_type, $name, $numcol, $row_num, "class='b'");
         foreach ($row as $ref => $val) {
            $val = $result[$itemtype][$ref];
            echo Search::showItem($display_type, ($val ? $val : ''), $numcol, $row_num, "class='right'");
            if ($itemtype!='total' && isset($result['total'])) {
               $result['total'][$ref] += $val;
            }
         }
         echo Search::showEndLine($display_type);
         $row_num++;

         $numcol=1;
         echo Search::showNewLine($display_type);
         echo Search::showItem($display_type, '', $numcol, $row_num);
         foreach ($row as $ref => $val) {
            $val = $result[$itemtype][$ref];
            $buy = $result[$itemtype]['buy'];
            if ($ref=='buy' || $buy==0 || $val==0) {
               $tmp = '';
            } else {
               $tmp = round($val*100/$buy,0)."%";
            }
            echo Search::showItem($display_type, $tmp, $numcol, $row_num, "class='right'");
         }
         echo Search::showEndLine($display_type);
         $row_num++;
      }

      if ($display_type == HTML_OUTPUT) {
         $row = array_pop($result); // Last line : total or single type
         unset($row['buy']);
         Stat::showGraph(array($title => $row), array('type' => 'pie'));
      }
   } else {
      $nbrows = 1; $nbcols = 1;
      echo Search::showHeader($display_type, $nbrows, $nbcols, true);
      echo Search::showNewLine($display_type);
      $num=1;
      echo Search::showHeaderItem($display_type, $LANG['search'][15], $num); // Nothing found
      echo Search::showEndLine($display_type);
   }
   echo Search::showFooter($display_type, $title);
}
if ($display_type == HTML_OUTPUT) {
   commonFooter();
}

?>
