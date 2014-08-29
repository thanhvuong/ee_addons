<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Proper Structure Channel Settings Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Thanh Vuong
 * @link		http://www.thanhvuong.com
 */

class Proper_structure_channel_settings {
	
	public $return_data;
	
	public function __construct()
	{
		$this->ee =& get_instance();
		$this->ee->load->add_package_path(PATH_THIRD.'proper_structure_channel_settings/');
		//$this->ee->load->library('proper');
	}

	
}
/* End of file mod.proper_structure_channel_settings.php */
/* Location: /system/expressionengine/third_party/proper_structure_channel_settings/mod.proper_structure_channel_settings.php */