<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// ------------------------------------------------------------------------
require_once PATH_THIRD . 'proper_structure_channel_settings/core/Proper.php';

/**
 * Proper Structure Channel Settings Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Thanh Vuong
 * @link		http://www.thanhvuong.com
 */

class Proper_structure_channel_settings_mcp {
	
	private $_cp_base;
	private $_cp_meth;
	private $_cp_qb;
	private $_rnav;
	private $_is_installed;
	private $_base_url;
	
	public function __construct()
	{
		$this->ee =& get_instance();
		$this->ee->load->helper('form');

		$this->_is_installed = Proper::is_installed();

		$this->_cp_base = Proper::get_base();
		$this->_cp_meth = Proper::get_method();
		$this->_cp_qb = Proper::get_qb();

		$this->_rnav = array(
			'base'		=> $this->_cp_base,
			'analysis'	=> $this->_cp_meth.'analysis',
		);
		
		$this->ee->cp->set_right_nav(array(
			'module_home'		=> $this->_rnav['base'],
			'module_analysis'	=> $this->_rnav['analysis'],
		));

	}
	
	public function index()
	{
		Proper::check_c();

		$this->ee->cp->set_variable('cp_page_title', lang('proper_structure_channel_settings_module_name'));

		if($this->_is_installed === FALSE) 
		{
			return $this->ee->load->view('_missing', array('missing' => lang('missing_module')), TRUE);
		}

		$d = array(
			'c'		=> Proper::gc()->result(),
			't'		=> Proper::gt()->result(),
			'ty'	=> Proper::gty(),
			'fa'	=> $this->_cp_qb.'update'
		);

		return $this->ee->load->view('index', $d, TRUE);
	}

	public function update()
	{
		Proper::update(array(
			$this->ee->input->post('sid'),
			$this->ee->input->post('hcid'),
			$this->ee->input->post('template'),
			$this->ee->input->post('type'),
			$this->ee->input->post('sa'),
			$this->ee->input->post('sips'),
			$this->ee->input->post('hcid'),
			$this->ee->input->post('sid'),
		));

		$this->ee->session->set_flashdata('message_success', $this->ee->lang->line('updated'));
		$this->ee->functions->redirect($this->_cp_meth.'index');
	}

	public function analysis()
	{
		$c = 1048576;
		$m = Proper::get_mt();
		$ml = Proper::get_ml() * $c;
		$mu =  memory_get_peak_usage();
		$est = round($m[2] * $c / $mu);
		$avg = round($m[0] * $c / $ml);
		$max = round($m[0] * $c / $mu);
		$eu = round($mu/1048576,2);

		$tally = 'Estimated requests remaining with current estimated memory use per request of '.$eu.'MB would be ' . $max . ' requests.';
		$this->ee->cp->set_variable('cp_page_title', lang('proper_structure_channel_settings_module_name'));
		return $this->ee->load->view('_missing', array('missing' => $tally), TRUE);
	}

}
/* End of file mcp.proper_structure_channel_settings.php */
/* Location: /system/expressionengine/third_party/proper_structure_channel_settings/mcp.proper_structure_channel_settings.php */