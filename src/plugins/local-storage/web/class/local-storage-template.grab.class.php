<?php
/**
 * local-storage-grab Select Resource
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class local_storage_template_grab
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'local_storage_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "local_storage_msg";
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param openqrm $openqrm
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($openqrm, $response) {
		$this->response = $response;
		$this->file     = $openqrm->file();
		$this->openqrm  = $openqrm;

		$this->response->add('lvol', $this->response->html->request()->get('lvol'));
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$table = $this->select();
		$t = $this->response->html->template($this->tpldir.'/local-storage-template-grab.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($table, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->add($this->lang['canceled'], 'canceled');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_tablebulider | htmlobject_div
	 */
	//--------------------------------------------
	function select() {

		$resource_id = $this->response->html->request()->get('resource_id');
		if($resource_id === '') {

			$d = array();

			$h = array();
			$h['resource_state']['title'] ='&#160;';
			$h['resource_state']['sortable'] = false;
			$h['resource_icon']['title'] ='&#160;';
			$h['resource_icon']['sortable'] = false;
			$h['resource_id']['title'] = $this->lang['table_id'];
			$h['resource_hostname']['title'] = $this->lang['table_name'];
			$h['resource_mac']['title'] = $this->lang['table_mac'];
			$h['resource_ip']['title'] = $this->lang['table_ip'];
			$h['resource_type']['title'] = $this->lang['table_type'];
			$h['resource_type']['sortable'] = false;
			$h['edit']['title'] = "&#160;";
			$h['edit']['sortable'] = false;

			$resource = new resource();
			$params  = $this->response->get_array($this->actions_name, 'grab');
			$b       = array();

			$table = $this->response->html->tablebuilder('resource', $params);
			$table->offset = 0;
			$table->sort = 'resource_id';
			$table->limit = 10;
			$table->order = 'ASC';
			$table->max = $resource->get_count('all');

			$table->init();

			$resources = $resource->display_overview(0, 10000, $table->sort, $table->order);
			foreach ($resources as $index => $resource_db) {
				// prepare the values for the array
				$resource = new resource();
				$resource->get_instance_by_id($resource_db["resource_id"]);

				if(	$resource->state === "active") {
					if ($resource->id == 0 || $resource->id !== $resource->vhostid) {
						continue;
					} else {
						$resource_mac = $resource_db["resource_mac"];
						$resource_icon_default="/openqrm/base/img/resource.png";
					}

					$virtualization = new virtualization();
					$virtualization->get_instance_by_id($resource->vtype);

					isset($resource_db["resource_hostname"]) ? $name = $resource_db["resource_hostname"] : $name = '&#160;';
					$state_icon="/openqrm/base/img/".$resource->state.".png";
					// idle ?
					if (($resource->imageid == "1") && ($resource->state == "active")) {
						$state_icon="/openqrm/base/img/idle.png";
					} else {
						continue;
					}
					if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$state_icon)) {
						$state_icon="/openqrm/base/img/unknown.png";
					}

					$a = $this->response->html->a();
					$a->title   = $this->lang['action_grab'];
					$a->label   = $this->lang['action_grab'];
					$a->handler = 'onclick="wait();"';
					$a->css     = 'grab';
					$a->href    = $this->response->get_url($this->actions_name, "grab").'&resource_id='.$resource->id;

					$b[] = array(
						'resource_state' => "<img width=24 height=24 src=$state_icon>",
						'resource_icon' => "<img width=24 height=24 src=$resource_icon_default>",
						'resource_id' => $resource_db["resource_id"],
						'resource_hostname' => $name,
						'resource_mac' => $resource_mac,
						'resource_ip' => $resource_db["resource_ip"],
						'resource_type' => $virtualization->name,
						'edit' => $a,
					);

				}
			}

			$table->id = 'Tabelle';
			$table->css = 'htmlobject_table';
			$table->border = 1;
			$table->form_action = $this->response->html->thisfile;
			$table->cellspacing = 0;
			$table->cellpadding = 3;
			$table->autosort = false;
			$table->sort_link = false;
			$table->max = count($b);
			$table->head = $h;
			$table->body = $b;
			$table->limit_select = array(
				array("value" => 10, "text" => 10),
				array("value" => 20, "text" => 20),
				array("value" => 30, "text" => 30),
				array("value" => 50, "text" => 50),
				array("value" => 100, "text" => 100),
			);
			return $table;
		} else {
			$this->__grab();
			$storage_id = $this->response->html->request()->get('storage_id');
			$template_export = '/'.$this->response->html->request()->get('volgroup').'/'. $this->response->html->request()->get('lvol');
			$msg = sprintf($this->lang['msg'], $resource_id, $storage_id.':'.$template_export);
			$this->response->redirect(
				$this->response->get_url($this->actions_name, 'volgroup', $this->message_param, $msg)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Send grab commands
	 *
	 * @access private
	 */
	//--------------------------------------------
	function __grab() {

		$storage = new storage();
		$storage->get_instance_by_id($this->response->html->request()->get('storage_id'));
		$storage_resource = new resource();
		$storage_resource->get_instance_by_id($storage->resource_id);
		$resource = new resource();
		$resource->get_instance_by_id($this->response->html->request()->get('resource_id'));

		$template_export = '/'.$this->response->html->request()->get('volgroup').'/'. $this->response->html->request()->get('lvol');

		// authenticating the storage volume
		$command  = $this->openqrm->get('basedir').'/plugins/local-storage/bin/openqrm-local-storage auth';
		$command .= ' -r '.$template_export;
		$command .= ' -i '.$resource->ip;
		$storage_resource->send_command($storage_resource->ip, $command);
		sleep(2);
		// we should create an authblocker here and wait until it is removed, anyway the image is existing in this case
		// send grab command
		$template_name = basename($template_export);
		$command  = $this->openqrm->get('basedir').'/plugins/local-storage/bin/openqrm-local-storage-manager grab';
		$command .= ' -m '.$resource->mac;
		$command .= ' -i '.$resource->ip;
		$command .= ' -n '.$template_name;
		$command .= ' -d '.$storage_resource->ip.":".$template_export;

		$openqrm_server = new openqrm_server();
		$openqrm_server->send_command($command);

		sleep(4);
		$resource->send_command($resource->ip, "reboot");

		// update resource
		$resource_fields["resource_state"]="transition";
		$resource->update_info($resource->id, $resource_fields);
	}

}
?>
