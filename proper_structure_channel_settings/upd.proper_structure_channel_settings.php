<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Proper Structure Channel Settings Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Thanh Vuong
 * @link		http://www.thanhvuong.com
 */

class Proper_structure_channel_settings_upd {
	
	public $version = '1.0';
	
	private $ee;
	
	public function __construct()
	{
		$this->ee =& get_instance();
	}
	

	public function install()
	{
		$mod_data = array(
			'module_name'			=> 'Proper_structure_channel_settings',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
		
		$this->ee->db->insert('modules', $mod_data);

		return TRUE;
	}

	public function uninstall()
	{
		$mod_id = $this->ee->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Proper_structure_channel_settings'
								))->row('module_id');
		
		$this->ee->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');
		
		$this->ee->db->where('module_name', 'Proper_structure_channel_settings')
					 ->delete('modules');

		$this->ee->db->where('class', 'Proper_structure_channel_settings_mcp')
					 ->delete('actions');		
		
		return TRUE;
	}
	

	public function update($current = '')
	{

		return TRUE;
	}
	
}
/* End of file upd.proper_structure_channel_settings.php */
/* Location: /system/expressionengine/third_party/proper_structure_channel_settings/upd.proper_structure_channel_settings.php */