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

plugin_reports_checkRight('reports', "rules", "r");


function plugin_reports_rulelist ($rulecollection, $title) {

   Session::checkRight($rulecollection::$right,"r");

   $rulecollection->getCollectionDatas(true, true);
   echo "<div class='center'>";
   echo "<table class='tab_cadre' cellpadding='5'>\n";
   echo "<tr><th colspan='6'><a href='".$_SERVER["PHP_SELF"]."'>" .
         //TRANS: The name of the report = Rule's catalog
         __('rules_report_title', 'reports') . "</a> - " . $title . "</th></tr>";

   echo "<tr><th>".__('Name')."</th>";
   echo "<th>".__('Description')."</th>";
   echo "<th colspan='2'>"._n('Criterion', 'Criteria', 2)."</th>";
   echo "<th>"._n('Action', 'Actions', 2)."</th>";
   echo "<th>".__('Active')."</th></tr>\n";

   foreach ($rulecollection->RuleList->list as $rule) {
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $rule->fields["name"] . "</td>";
      echo "<td>" . $rule->fields["description"] . "</td>";

      if ($rule->fields["match"] == Rule::AND_MATCHING) {
         echo "<td>".__('and')."</td>";
      } else {
         echo "<td>".__('or')."</td>";
      }

      echo "<td>";
      foreach ($rule->criterias as $criteria) {
         echo $rule->getCriteriaName($criteria->fields["criteria"]) . " " .
              RuleCriteria::getConditionByID($criteria->fields["condition"], get_class($rule))." ".
              $rule->getCriteriaDisplayPattern($criteria->fields["criteria"],
                                               $criteria->fields["condition"],
                                               $criteria->fields["pattern"]) .
              "<br>";
      }
      echo "</td>";
      echo "<td>";
      foreach ($rule->actions as $action) {
         echo $rule->getActionName($action->fields["field"]) . " " .
               RuleAction::getActionByID($action->fields["action_type"]) . " " .
               stripslashes($rule->getActionValue($action->fields["field"],$action->fields["action_type"],
                            $action->fields["value"])) .
               "<br>";
      }
      echo "</td>";

      if ($rule->fields["is_active"]) {
         echo "<td>".__('Yes')."</td>";
      } else {
         echo "<td>".__('No')."</td>";
      }
      echo "</tr>\n";
   }
   echo "</table></div>\n";
}

Html::header(__("rules_report_title", 'reports'), $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

$type = (isset($_GET["type"]) ? $_GET["type"] : "");

if ($type == "ldap") {
   $rulecollection = new RuleRightCollection();
   plugin_reports_rulelist($rulecollection, __('Authorizations assignment rules'));

} else if ($type == "ocs") {
   $rulecollection = new RuleOcsCollection(-1);
   plugin_reports_rulelist($rulecollection, __('Rules for assigning a computer to an entity'));

} else if ($type == "track") {
   $rulecollection = new RuleTicketCollection();
   plugin_reports_rulelist($rulecollection, __('Business rules for tickets'));

} else if ($type == "soft") {
   $rulecollection = new RuleSoftwareCategoryCollection();
   plugin_reports_rulelist($rulecollection, __('Rules for assigning a category to software'));

} else {
   echo "<div class='center'>";
   echo "<table class='tab_cadre' cellpadding='5'>\n";
   echo "<tr><th>". sprintf(__('%1$s - %2$s'), __("rules_report_title", 'reports'), __('Rule type'))."</th></tr>";
/*
   if ($CFG_GLPI["use_ocs_mode"] && Session::haveRight("rule_ocs","r")) {
      echo "<tr class='tab_bg_1'><td class='center b'>".
           "<a href='".$_SERVER["PHP_SELF"]."?type=ocs'>".$LANG["rulesengine"][18]."</a></td></tr>";
   }
*/
   if (Session::haveRight("rule_ldap","r")) {
      echo "<tr class='tab_bg_1'><td class='center b'>".
           "<a href='".$_SERVER["PHP_SELF"]."?type=ldap'>".__('Authorizations assignment rules').
           "</a></td></tr>";
   }

   if (Session::haveRight("rule_tracking","r")) {
      echo "<tr class='tab_bg_1'><td class='center b'>".
           "<a href='".$_SERVER["PHP_SELF"]."?type=track'>".__('Business rules for tickets').
           "</a></td></tr>";
   }

   if (Session::haveRight("rule_softwarecategories","r")) {
      echo "<tr class='tab_bg_1'><td class='center b'>".
           "<a href='".$_SERVER["PHP_SELF"]."?type=soft'>".
             __('Rules for assigning a category to software')."</a></td></tr>";
   }
   echo "</table></div>\n";
}

Html::footer();
?>