<?php

/**
 * @version $Id$
-------------------------------------------------------------------------
LICENSE

@package   reports - additional report - List of equipment in entity
@authors    Michał Panasiewicz
@copyright Copyright (c) 2022-2022
@license
@link      https://github.com/yllen/reports
@link      http://www.glpi-project.org/
@since     2022

List of permissions:
- Management -> Financial & Administratives Information -> Read
- Assets -> Read
- Tags -> Read
--------------------------------------------------------------------------
 */

$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0;

include '../../../../inc/includes.php';

//TRANS: The name of the report = List of assets in entity
$report = new PluginReportsAutoReport(
	__('List of equipment in entity', 'reports')
);
//TODO - multichoice for Entities
$entities = new PluginReportsEntitiesCriteria($report);

// TODO - configuration for multiple assets, configuration for report
$ignored = [
	'Cartridge',
	'CartridgeItem',
	'Consumable',
	'ConsumableItem',
	'Software',
	'Line',
	'Certificate',
	'Appliance',
	'Domain',
	'Item_DeviceSimcard',
	'SoftwareLicense',
	'Peripheral',
	'Phone',
	'Rack',
	'Enclosure',
	'PDU',
	'PassiveDCEquipment',
	'DatabaseInstance',
	'Cable'
];

$report->setColumns([
	new PluginReportsColumnType('itemtype', __('Assets')),
	new PluginReportsColumnLink('entityid', __('Entity'), 'Entity', [
		'sorton' => 'glpi_entities.completename',
	]),
	new PluginReportsColumnLink('locationid', __('Location'), 'Location', [
		'sorton' => 'glpi_locations.completename',
	]),
	new PluginReportsColumnTypeLink('items_id', __('Item'), 'itemtype', [
		'with_comment' => 1,
	]),
	new PluginReportsColumnDate('date_creation', __('Creation date'), [
		'sorton' => 'date_creation',
	]),
	new PluginReportsColumnDate(
		'lastinventorydate',
		__('Date of last physical inventory'),
		['sorton' => 'lastinventorydate']
	),
	new PluginReportsColumnLink(
		'manufacturerid',
		__('Manufacturer'),
		'Manufacturer',
		['sorton' => 'glpi_manufacturers.name']
	),
	new PluginReportsColumnModelType('models_id', __('Model'), 'itemtype', [
		'with_comment' => 1,
	]),
	new PluginReportsColumn('otherserial', __('Inventory number')),
	new PluginReportsColumn('serial', __('Serial number')),
	new PluginReportsColumn('invoicenumber', __('Invoice number')),
	new PluginReportsColumn(
		'entitytag',
		__('Type') . ' ' . _n('Entity', 'Entities', 2)
	),
	new PluginReportsColumn('statename', __('Status')),
	new PluginReportsColumnDate('date_mod', __('Last update')),
	new PluginReportsColumnTypeType('types_id', __('Type'), 'itemtype', [
		'with_comment' => 1,
	]),
]);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated() && $entities->getParameterValue() != 0) {
	$report->setSubNameAuto();
	$cfg_types = array_values(array_diff($CFG_GLPI['infocom_types'], $ignored));
	$query = getSqlSubRequest($cfg_types[0], $entities, new $cfg_types[0]());
	
	foreach ($cfg_types as $itemtype) {
		$obj = new $itemtype();
		if (
			$obj->isField('entities_id') &&
			$itemtype != $cfg_types[0] &&
			!in_array($itemtype, $ignored)
		) {
			$query .=
				'UNION (' . getSqlSubRequest($itemtype, $entities, $obj) . ')';
		}
	}
	
	$report->setGroupBy('entity', 'itemtype');
	//$report->getOrderBy('name');
	$report->setSqlRequest($query);
	$report->execute();
} else {
	echo "<p class='red center'>" .
		__('Entity not selected', 'reports') .
		'</p>';
	Html::footer();
}

function getSqlSubRequest($itemtype, $entities, $obj) {
	$dbu = new DbUtils();
	
	$table = $dbu->getTableForItemType($itemtype);
	$models_id = $dbu->getForeignKeyFieldForTable(
		$dbu->getTableForItemType($itemtype . 'Model')
	);
	$types_id = $dbu->getForeignKeyFieldForTable(
		$dbu->getTableForItemType($itemtype . 'Type')
	);
	$fields = [
		'name' => 'name',
		'serial' => 'serial',
		'otherserial' => 'otherserial',
		'states_id' => 'states_id',
		'date_creation' => 'date_creation',
		'date_mod' => 'date_mod',
		'locations_id' => 'locations_id',
		'entities_id' => 'entities_id',
		'manufacturers_id' => 'manufacturers_id',
		'id' => 'id',
		$models_id => 'models_id',
		$types_id => 'types_id',
	];
	
	$query_where = "SELECT '$itemtype' AS itemtype,
						   `$table`.`id` AS items_id,
						   `$table`.`entities_id`";
	
	$join = '';
	foreach ($fields as $field => $alias) {
		if ($obj->isField($field)) {
			if ($field == 'locations_id') {
				$query_where .= ', `glpi_locations`.`id` AS locationid';
				$join .= " LEFT JOIN `glpi_locations`ON `glpi_locations`.`id` = `$table`.`locations_id` ";
			} elseif ($field == 'entities_id') {
				$query_where .= ', `glpi_entities`.`id` AS entityid';
				$join .= " LEFT JOIN `glpi_entities` ON `glpi_entities`.`id` = `$table`.`entities_id` ";
				//Tag for entity
				$query_where .= ', `glpi_plugin_tag_tags`.`name` AS entitytag';
				$join .= " LEFT JOIN `glpi_plugin_tag_tagitems` ON  (`glpi_entities`.`id` = `glpi_plugin_tag_tagitems`.`items_id`
					   AND `glpi_plugin_tag_tagitems`.`itemtype` = 'Entity' ) ";
				$join .=
					' LEFT JOIN `glpi_plugin_tag_tags` ON `glpi_plugin_tag_tagitems`.`plugin_tag_tags_id` = `glpi_plugin_tag_tags`.`id` ';
			} elseif ($field == 'manufacturers_id') {
				$query_where .= ', `glpi_manufacturers`.`id` AS manufacturerid';
				$join .= " LEFT JOIN `glpi_manufacturers` ON `glpi_manufacturers`.`id` = `$table`.`manufacturers_id` ";
			} elseif ($field == 'id') {
				$query_where .= ', `glpi_infocoms`.`bill` AS invoicenumber';
				$query_where .=
					', `glpi_infocoms`.`inventory_date` AS lastinventorydate';
				$join .= " LEFT JOIN `glpi_infocoms` ON ( `glpi_infocoms`.`items_id` = `$table`.`id` AND `glpi_infocoms`.`itemtype` = '$itemtype' ) ";
			} elseif ($field == 'states_id') {
				$query_where .= ', `glpi_states`.`name` AS statename';
				$join .= " LEFT JOIN `glpi_states` ON `glpi_states`.`id` = `$table`.`states_id` ";
			} else {
				$query_where .= ", `$table`.`$field` AS $alias";
			}
		} else {
			$query_where .= ", '' AS $alias";
		}
	}
	
	$query_where .= " FROM `$table`
	$join ";
	
	if ($obj->isEntityAssign()) {
		$query_where .= $dbu->getEntitiesRestrictRequest('WHERE', "$table");
	} else {
		$query_where .= 'WHERE 1';
	}
	
	if ($obj->maybeTemplate()) {
		$query_where .= " AND `$table`.`is_template`='0'";
	}
	
	if ($obj->maybeDeleted()) {
		$query_where .= " AND `$table`.`is_deleted`='0'";
	}
	
	$query_where .= $entities->getSqlCriteriasRestriction('AND', $table);
	return $query_where;
}
