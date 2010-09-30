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

$RootDir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/';
require_once "$RootDir/include/user.inc.php";
require_once "$RootDir/class/htmlobject.openqrm.class.php";
require_once "$RootDir/plugins/wakeuponlan/class/wakeuponlan.class.php";
require_once "$RootDir/plugins/wakeuponlan/wakeuponlan-htmlobjects.php";
require_once "$RootDir/class/image.class.php";
require_once "$RootDir/class/resource.class.php";
require_once "$RootDir/class/appliance.class.php";
require_once "$RootDir/class/kernel.class.php";
require_once "$RootDir/class/virtualization.class.php";
require_once "$RootDir/class/openqrm_server.class.php";
require_once "$RootDir/include/openqrm-server-config.php";
global $OPENQRM_SERVER_BASE_DIR;
global $OPENQRM_EXEC_PORT;
$openqrm_server = new openqrm_server();
$OPENQRM_SERVER_IP_ADDRESS=$openqrm_server->get_ip_address();
global $OPENQRM_SERVER_IP_ADDRESS;
global $openqrm_server;
$event = new event();
global $event;


class wakeuponlan_controller
{


	function wakeuponlan_controller() {

        $this->db       = new wakeuponlan();
		$this->thisfile = basename($_SERVER['PHP_SELF']);
		$this->html     = new htmlobject_openqrm();
		$this->html->debug();

		$this->http        = $this->html->http();
		$this->form        = $this->html->form();
		$this->formbuilder = new wakeuponlan_htmlobject($this->html, $this->thisfile);
		$this->tabs        = $this->formbuilder->get_tabs();

		$this->tpldir = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base/plugins/wakeuponlan/tpl';
		$this->init($this->http->get_request('action'));
	}

	function init( $action = null ) {
        switch($action) {
            case '':
            case 'wakeup':
                $this->action = 'wakeup';
            break;
            default:
                $this->action = 'wakeup';
            break;
        }
	}

	//------------------------------------------
	// Getters
	//------------------------------------------

	function get_object() {
		switch($this->action) {
			case 'wakeup':
				return $this->wakeup();
			break;
		}
	}

	function get_template() {
		switch($this->action) {
			case 'wakeup':
				$form = $this->wakeup();
				$vars = array_merge(
					$form->get_object(), 
					array(
					'thisfile' => $this->thisfile,
					));
				$t = $this->html->template($this->tpldir.'/wakeuponlan-resource-list.tpl.php');
				$t->init($vars);
				return $t;
			break;
		}
	}

	function get_tabs() {
		$tabs = $this->tabs;
		switch($this->action) {
			case 'wakeup':
				$tpl   = $this->get_template();
				$tabs->_tabs['wakeup']['value'] = $tpl->get_string();
				$tabs->_tabs['wakeup']['active'] = true;
				$tabs->init($tabs->_tabs);
				return $tabs;
			break;
		}
	}

	function get_string() {
		$tabs = $this->get_tabs();
		$vars = array_merge( 
			array(
			'title' => 'WakeUpOnLAN',
			'tabs' => $tabs->get_string(),
			));
		$t = $this->html->template($this->tpldir.'/wakeuponlan-index.tpl.php');
		$t->init($vars);
		return $t->get_string();
	}

	//------------------------------------------
	// Actions
	//------------------------------------------

	
	function wakeup() {
        global $event;
        global $OPENQRM_SERVER_BASE_DIR;
        global $OPENQRM_SERVER_IP_ADDRESS;
        global $OPENQRM_EXEC_PORT;
        global $openqrm_server;

		$html  = $this->formbuilder;		
		$table = $html->get_list_resources();
        
        // here we run the action
        $wakeuponlan_action = $this->http->get_request('action');

        $id = $this->http->get_request('resource_id');
        if ($id > 0) {
            $resource_wakeup = new resource();
            $resource_wakeup->get_instance_by_id($id);
            $resource_wakeup_mac = $resource_wakeup->mac;
            switch ($wakeuponlan_action) {
                case 'wakeup':
                    $event->log("wakeup", $_SERVER['REQUEST_TIME'], 5, "wakeuponlan-controller.php", "Waking up $resource_wakeup->id", "", "", 0, 0, $resource_wakeup->id);
                    $wol_command = "$OPENQRM_SERVER_BASE_DIR/openqrm/plugins/wakeuponlan/bin/openqrm-wakeuponlan wakeup -m $resource_wakeup_mac";
                    $openqrm_server->send_command($wol_command);
                    // set state to transition
                    $resource_fields=array();
                    $resource_fields["resource_state"]="transition";
                    $resource_wakeup->update_info($id, $resource_fields);
                    break;

                case 'sleep':
                    $ip = $resource_wakeup->ip;
                    $resource_wakeup->send_command("$ip", "halt");
                    // set state to off
                    $resource_fields=array();
                    $resource_fields["resource_state"]="off";
                    $resource_wakeup->update_info($id, $resource_fields);
                    break;

                case 'enable':
                    $resource_wakeup->set_resource_capabilities("SFO", "1");
                    break;
                case 'disable':
                    $resource_wakeup->set_resource_capabilities("SFO", "0");
                    break;

            }
        }

        // here we construct the resource table
		$resource_tmp = new resource();
		$arBody = array();
		$resource_array = $resource_tmp->display_overview($table->offset, $table->limit, $table->sort, $table->order);

		if(count($resource_array) > 0) {

			foreach ($resource_array as $index => $resource_db) {
                $resource_action = "";
				$resource = new resource();
				$resource->get_instance_by_id($resource_db["resource_id"]);
                // state
                $resource_icon_default="/openqrm/base/img/resource.png";
                $state_icon="/openqrm/base/img/$resource->state.png";
                // idle ?
                if (("$resource->imageid" == "1") && ("$resource->state" == "active")) {
                    $state_icon="/openqrm/base/img/idle.png";
                }

                # openQRM special resource
                if ($resource_db["resource_id"] == 0) {
                    $resource_mac = "x:x:x:x:x:x";
                    $resource_virtualization_type = "openQRM";
                    $resource_icon_default="/openqrm/base/img/logo.png";
                    $resource_action = "";
                } else {
                    $resource_mac = $resource_db["resource_mac"];
                    // type
                    $virtualization = new virtualization();
                    $virtualization->get_instance_by_id($resource->vtype);
                    $resource_virtualization_type=$virtualization->name;

                    // enable/disable
                    $wol_state = $resource->get_resource_capabilities("SFO");
                    if ($wol_state == 1) {
                        $resource_wol = '<a href="'.$this->thisfile.'?action=disable&amp;resource_id='.$resource_db["resource_id"].'"><strong>enabled</strong></a>';
                        // actions
                        if ($resource->state == "off") {
                            // only phys. systems support wakeuponlan
                            if ($resource->vtype == 1) {
                                $resource_action = '<a href="'.$this->thisfile.'?action=wakeup&amp;resource_id='.$resource_db["resource_id"].'"><strong>wake-up</strong></a>';
                            }
                        } else if ($resource->state == "active") {
                           if ($resource->imageid == "1") {
                                $resource_action = '<a href="'.$this->thisfile.'?action=sleep&amp;resource_id='.$resource_db["resource_id"].'"><strong>sleep</strong></a>';
                           }
                        } else {
                                $resource_action = "";
                                $state_icon="/openqrm/base/img/transition.png";
                        }

                    } else {
                        $resource_wol = '<a href="'.$this->thisfile.'?action=enable&amp;resource_id='.$resource_db["resource_id"].'"><strong>disabled</strong></a>';
                        $resource_action = "";
                    }


                }

				$arBody[] = array(
					'resource_state' => "<img src=$state_icon>",
					'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
					'resource_id' => $resource_db["resource_id"],
					'resource_name' => $resource_db["resource_hostname"],
					'resource_mac' => $resource_mac,
					'resource_ip' => $resource_db["resource_ip"],
					'resource_type' => $resource_virtualization_type,
					'resource_wol' => $resource_wol,
					'resource_action' => $resource_action,
				);

			}

			$table->body = $arBody;
			$table->max = $resource_tmp->get_count();
			$this->form->add($table, 'table');
		} else {
			$div = $this->html->div();
			$div->add('Please add physical resources first');
			$this->form->add($div, 'table');
		}

		$this->form->add($this->formbuilder->get_form('resource_list'), 'formbuilder');
		return $this->form;


	}



}
?>
