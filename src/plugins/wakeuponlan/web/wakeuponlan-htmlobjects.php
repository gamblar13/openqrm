<?php

/*
  This file is part of openQRM.

    openQRM is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2
    as published by the Free Software Foundation.

    openQRM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

    Copyright 2010, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/


class wakeuponlan_htmlobject
{

var $lang = array(
		'tab_resource_list' => 'Resource List',

	);

	function wakeuponlan_htmlobject( $html, $thisfile ) {
		$this->html     = $html;
		$this->thisfile = $thisfile;
	}

    function get_form($mode = 'select', $data = null) {
        $d = array();
		$d['wakeuponlan_action']['static']                    = true;
        $d['wakeuponlan_action']['object']['type']            = 'htmlobject_input';
        $d['wakeuponlan_action']['object']['attrib']['type']  = 'hidden';
        $d['wakeuponlan_action']['object']['attrib']['id']    = 'wakeuponlan_action';
        $d['wakeuponlan_action']['object']['attrib']['name']  = 'action';
        $d['wakeuponlan_action']['object']['attrib']['value'] = $mode;

		$form = $this->html->formbuilder();
		$form->init($d);

		return $form;

    }



	function get_list_resources() {

		$table = $this->html->tablebuilder('resource_id', '', '', '', 'select');

		$arHead['resource_state']['title'] ='';
		$arHead['resource_state']['sortable'] = false;

		$arHead['resource_icon']['title'] ='';
		$arHead['resource_icon']['sortable'] = false;

		$arHead['resource_id']['title'] ='ID';

		$arHead['resource_name']['title'] ='Name';

		$arHead['resource_mac']['title'] ='MAC';

		$arHead['resource_ip']['title'] ='IP';

		$arHead['resource_type']['title'] ='Type';
		$arHead['resource_type']['sortable'] = false;

		$arHead['resource_wol']['title'] ='WOL';
		$arHead['resource_wol']['sortable'] = false;

		$arHead['resource_action']['title'] ='Action';
		$arHead['resource_action']['sortable'] = false;

		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->head = $arHead;
		return $table;

	}

	function get_tabs() {

		$content['wakeup']['label']  = $this->lang['tab_resource_list'];
		$content['wakeup']['target'] = $this->thisfile;
		$content['wakeup']['onclick'] = false;
		$content['wakeup']['request'] = array('action' => 'resource_list');

		$tabs = $this->html->tabmenu($content, 'wakeup');
		$tabs->css = 'htmlobject_tabs';
		return $tabs;

	}

}
?>
