<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Proper Structure Channel Settings Abstract Class
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Thanh Vuong
 * @link		http://www.thanhvuong.com
 */

abstract class Proper
{


	private function _update($params = array())
	{
		$sql = 'UPDATE exp_structure_channels SET site_id = ?, channel_id = ?, template_id = ?, type = ?, split_assets = ?, show_in_page_selector = ? WHERE channel_id = ? AND site_id = ?';
		return $this->ee->db->query($sql, $params);
	}

	private function _gt()
	{
		$sql = 'SELECT et.template_id AS tid, CONCAT(etg.group_name, \'/\', et.template_name) AS tp FROM exp_templates AS et LEFT JOIN exp_template_groups AS etg ON etg.group_id = et.group_id ORDER BY tp ASC';
		return $this->ee->db->query($sql);

	}

	private function _gc()
	{
		$sql = 'SELECT esc.site_id AS sid, esc.channel_id AS cid, esc.type, esc.split_assets AS sa, esc.show_in_page_selector AS sips, ec.channel_title AS ct, CONCAT(etg.group_name, \'/\', et.template_name) AS tp, esc.template_id AS tid FROM exp_structure_channels AS esc LEFT JOIN exp_channels AS ec ON esc.channel_id = ec.channel_id LEFT JOIN exp_templates AS et ON esc.template_id = et.template_id LEFT JOIN exp_template_groups AS etg ON etg.group_id = et.group_id WHERE ec.channel_title IS NOT NULL ORDER BY ec.channel_title ASC';
		return $this->ee->db->query($sql);
	}

	private function _gty()
	{
		$sql = 'SHOW COLUMNS FROM exp_structure_channels WHERE FIELD = \'type\'';
		$r = $this->ee->db->query($sql);
		if($r->num_rows == 0){
			return false;
		}

		$r = $r->row();
		preg_match('/^enum\((.*)\)$/', $r->Type, $matches);

	    foreach( explode(',', $matches[1]) as $value )
	    {
	         $enum[] = trim( $value, "'" );
	    }
	    return $enum;
		
	}

	private function _is_installed()
	{
		$sql = 'SELECT module_id FROM exp_modules WHERE module_name = ?';
		$r = $this->ee->db->query($sql, array('Structure'));
		return ($r->num_rows > 0) ? true : false;
	}

	private function _get_mt()
	{
		exec('free -mo', $out);
		preg_match_all('/\s+([0-9]+)/', $out[1], $matches);
		list($total, $used, $free, $shared, $buffers, $cached) = $matches[1];
	    return array($total, $used, $free);
	}

	private function _get_ml()
	{
		return ini_get('memory_limit');
	}


	public function check_c()
	{
		$sql = 'SELECT channel_id FROM exp_channels WHERE channel_id NOT IN(SELECT channel_id FROM exp_structure_channels)';
		$r = $this->ee->db->query($sql);
		if($r->num_rows < 0){ 
			return false;
		}

		foreach($r->result() as $c)
		{
			$this->ee->db->insert('exp_structure_channels', 
				array(
					'site_id' => 1,
					'channel_id' => $c->channel_id,
					'template_id' => 0,
					'type' => 'unmanaged',
					'split_assets' => 'n',
					'show_in_page_selector' => 'y'
				)
			);
		}

		return $r->result(); 


	}

	public function gt()
	{
		return self::_gt();
	}

	public function gty()
	{
		return self::_gty();
	}

	public function gc()
	{
		return self::_gc();
	}

	public function is_installed()
	{
		return self::_is_installed();
	}

	public function get_base()
	{
		return BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=proper_structure_channel_settings';
	}

	public function get_method()
	{
		return BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=proper_structure_channel_settings'.AMP.'method=';
	}

	public function get_qb()
	{
		return 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=proper_structure_channel_settings'.AMP.'method=';
	}

	public function get_mt()
	{
		return self::_get_mt();
	}

	public function get_ml()
	{
		return self::_get_ml();
	}

	public function update($params = array())
	{
		return self::_update($params);
	}

}

