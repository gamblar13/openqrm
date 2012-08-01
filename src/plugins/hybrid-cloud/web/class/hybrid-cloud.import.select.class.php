<?php
/**
 *  Hybrid-cloud import select
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class hybrid_cloud_import_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'hybrid_cloud_import_action';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'hybrid_cloud_import_identifier';
/**
* message param
* @access public
* @var string
*/
var $message_param = "hybrid_cloud_import_msg";
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
	function __construct($openqrm, $response, $controller) {
		$this->response   = $response;
		$this->file       = $openqrm->file();
		$this->openqrm    = $openqrm;
		$this->controller = $controller;
		$this->id         = $this->response->html->request()->get('hybrid_cloud_id');
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
		$data = $this->select();
		$t = $this->response->html->template($this->tpldir.'/hybrid-cloud-import-select.tpl.php');
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($data);
		$t->add($this->response->get_array());
		$t->add(sprintf($this->lang['label'], $data['name']), 'label');
		$t->add($this->controller->prefix_tab, 'prefix_tab');
		$t->add($this->openqrm->get('baseurl'), 'baseurl');
		$t->add($this->lang['please_wait'], 'please_wait');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select() {

		$h = array();
		$h['id']['title'] = $this->lang['table_id'];
		$h['type']['title'] = $this->lang['table_type'];
		$h['host']['title'] = $this->lang['table_host'];
		$h['ame']['title'] = $this->lang['table_ami'];
		$h['state']['title'] = $this->lang['table_state'];
		$h['edit']['title'] = '&#160;';
		$h['edit']['sortable'] = false;

		$content = array();
		$file = $this->openqrm->get('basedir').'/plugins/hybrid-cloud/web/stat/'.$this->id.'.describe_instances.log';
		if($this->file->exists($file)) {
			$this->file->remove($file);
		}

		require_once($this->openqrm->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$hc = new hybrid_cloud();
		$hc->get_instance_by_id($this->id);

		$command  = $this->openqrm->get('basedir').'/plugins/hybrid-cloud/bin/openqrm-hybrid-cloud describe_instances';
		$command .= ' -i '.$hc->id;
		$command .= ' -n '.$hc->account_name;
		$command .= ' -c '.$hc->rc_config;
		$command .= ' -t '.$hc->account_type;

		$server = new openqrm_server();
		$server->send_command($command);

		while (!$this->file->exists($file))
		{
		  usleep(10000); // sleep 10ms to unload the CPU
		  clearstatcache();
		}

		$content = $this->file->get_contents($file);
		$content = explode("\n", $content);

		$b = array();
		foreach ($content as $k => $v) {
			if($v !== '') {
				$tmp   = explode('@', $v);
				$type  = $tmp[0];
				$id    = $tmp[1];
				$ame   = $tmp[2];
				$host  = $tmp[3];
				$state = $tmp[5];

				// edit
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_import'];
				$a->handler = 'onclick="wait();"';
				$a->css     = 'import';
				$a->href    = $this->response->get_url($this->actions_name, 'target').'&instance_id='.$id;

				$b[] = array(
					'id' => $id,
					'host' => $host,
					'ame' => $ame,
					'type' => $type,
					'state' => $state,
					'edit' => $a->get_string(),
				);
			}
		}

		$params = $this->response->get_array($this->actions_name, 'select');
		$table = $this->response->html->tablebuilder('source', $params);
		$table->offset = 0;
		$table->sort = 'id';
		$table->limit = 10;
		$table->order = 'ASC';
		$table->id = 'Tabelle';
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 3;
		$table->autosort = true;
		$table->sort_link = false;
		$table->max = count($b);
		$table->head = $h;
		$table->body = $b;

		// handle account name
		require_once($this->openqrm->get('basedir').'/plugins/hybrid-cloud/web/class/hybrid-cloud.class.php');
		$hc = new hybrid_cloud();
		$hc->get_instance_by_id($this->id);

		$d['name']  = $hc->account_name;
		$d['form']  = $this->response->get_form($this->actions_name, 'select', false)->get_elements();
		$d['table'] = $table;

		return $d;
	}

}
?>
