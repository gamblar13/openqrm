<?php
/**
 * openQRM Class
 *
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
 */

class openqrm
{
/**
* Absolute path to istall dir
* @access protected
* @var string
*/
protected $basedir;
/**
* Absolute path to class dir
* @access protected
* @var string
*/
protected $classdir;
/**
* Absolute path to web dir
* @access protected
* @var string
*/
protected $webdir;
/**
* Absolute uri
* @access protected
* @var string
*/
protected $baseurl;
/**
* openQRM config
* @access protected
* @var string
*/
protected $config;
/**
* DB object
* @access private
* @var object
*/
private $db;
/**
* file object
* @access private
* @var object
*/
private $file;
/**
* admin user object
* @access private
* @var object
*/
private $admin;
/**
* current user object
* @access private
* @var object
*/
private $user;
/**
* name of db tables
* @access private
* @var array
*/
private $table;


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct() {
		if ((file_exists("/etc/init.d/openqrm")) && (is_link("/etc/init.d/openqrm"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/openqrm"))));
		} else {
			$this->basedir = "/usr/share/openqrm";
		}
		$this->classdir = $this->basedir.'/web/base/class';
		$this->webdir   = $_SERVER["DOCUMENT_ROOT"].'/openqrm/base';
		$this->baseurl  = '/openqrm/base';
		$this->config   = $this->parse_conf($this->basedir.'/etc/openqrm-server.conf', 'OPENQRM_');
		
		$this->table['appliance']      = 'appliance_info';
		$this->table['deployment']     = 'deployment_info';
		$this->table['event']          = 'event_info';
		$this->table['image']          = 'image_info';
		$this->table['kernel']         = 'kernel_info';
		$this->table['resource']       = 'resource_info';
		$this->table['storage']        = 'storage_info';
		$this->table['virtualization'] = 'virtualization_info';

		// load file object
		$this->file();

	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
	    require_once($this->classdir.'/appliance.class.php');
	    require_once($this->classdir.'/deployment.class.php');
	    require_once($this->classdir.'/event.class.php');
	    require_once($this->classdir.'/image.class.php');
	    require_once($this->classdir.'/kernel.class.php');
	    require_once($this->classdir.'/plugin.class.php');
	    require_once($this->classdir.'/resource.class.php');
	    require_once($this->classdir.'/storage.class.php');
	    require_once($this->classdir.'/virtualization.class.php');
	}

	//--------------------------------------------
	/**
	 * Get object attributes
	 *
	 * @access public
	 * @param string $attrib name of attrib to return
	 * @param string $key name of attrib key to return
	 * @return mixed
	 */
	//--------------------------------------------
	function get($attrib, $key = null) {
		if(isset($this->$attrib)) {
			$attrib = $this->$attrib;
			if(isset($key)) {
				if(isset($attrib[$key])) {
					return $attrib[$key];
				}
			} else {
				return $attrib;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Get db object
	 *
	 * @access protected
	 * @return object db
	 */
	//--------------------------------------------
	protected function db() {
		if(!isset($this->db)) {
			require_once($this->classdir.'/db.class.php');
			$this->db = new db($this);
		}
		return $this->db;
	}

	//--------------------------------------------
	/**
	 * Get file object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function file() {
		if(!isset($this->file)) {
			require_once($this->classdir.'/file.handler.class.php');
			$this->file = new file_handler();
		}
		$this->file->lang = $this->user()->translate($this->file->lang, $this->basedir."/web/base/lang", 'file.handler.ini');
		return $this->file;
	}

	//--------------------------------------------
	/**
	 * Get admin object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function admin() {
		if(!isset($this->admin)) {
			require_once($this->classdir.'/user.class.php');
			$admin =  new user('openqrm');
			$admin->set_user();
			$this->admin = $admin;
		}
		return $this->admin;
	}


	//--------------------------------------------
	/**
	 * Get user object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function user() {
		if(!isset($this->user)) {
			require_once($this->classdir.'/user.class.php');
			$user =  new user($_SERVER['PHP_AUTH_USER']);
			$user->set_user();
			$this->user = $user;
		}
		return $this->user;
	}

	//--------------------------------------------
	/**
	 * Parse an openqrm config file
	 *
	 * @access public
	 * @param string $path
	 * @param string $replace
	 * @return array
	 */
	//--------------------------------------------
	function parse_conf ( $path, $replace = null ) {
		if(file_exists($path)) {
			$ini = file( $path );
			if ( count( $ini ) == 0 ) { return array(); }
			$globals = array();
			foreach( $ini as $line ){
				$line = trim( $line );
				// Comments
				if ( $line == '' || $line{0} != 'O' ) { continue; }
				// Key-value pair
				list( $key, $value ) = explode( '=', $line, 2 );
				$key = trim( $key );
				if(isset($replace)) {
					$key = str_replace($replace, "", $key );
				}
				$value = trim( $value );
				$value = str_replace("\"", "", $value );
				$globals[ $key ] = $value;
			}
			return $globals;
		}
	}



}
