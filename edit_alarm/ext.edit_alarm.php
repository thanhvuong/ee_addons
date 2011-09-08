<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Edit Alarm
 *
 * This extension alerts authors when another author is editing a resource they are accessing.
 * For EE 1.x, use "Edit Alert" originally created by ExpressionEngine at http://expressionengine.com/downloads/details/edit_alert/
 * Thanks to Derek Jones for giving me permission to port the old extension over to EE2.x.
 *
 * Contributors:
 * Thanks to Brian Litzinger for contributing code for EditAlarm v1.2
 *
 * @package   Edit Alarm
 * @author    Thanh Vuong <thanh@vuongs.org>
 * @link      http://www.thanhvuong.com/dev/
 * @copyright Copyright (c) 2010 Thanh Vuong
 * @license   http://creativecommons.org/licenses/by-sa/3.0/   Attribution-Share Alike 3.0 Unported
 * @ide       JetBrains PhpStorm
 */

//Edit Alarm Class
class Edit_alarm_ext {

	var $settings = array();
	var $name = 'Edit Alarm';
	var $version = '1.2';
	var $description = 'Displays alerts when attempting to edit a resource that another author is currently editing.';
	var $settings_exist = 'y';
	var $docs_url = 'http://www.thanhvuong.com/dev/';

	var $add = FALSE;	// used to manage whether or not to add an alert
	var $entry_id = '';
	var $disable_msg = '';
	var $disable = '';


	//Constructor
	function Edit_alarm_ext($settings='')
	{
		$this->EE =& get_instance();
        $this->settings = $settings;

	}

	/**
	 * Display Entry Alert
	 *
	 * Display and set alerts for weblog entries
	 *
	 * @access	public
	 * @param	array 	array of entry data
	 * @return	string
	 */
    function display_entry_alert( $result=array() )
	{


		//Only take action if they have enabled alerts for entries
		if (! in_array('e', $this->settings['apply_to']))
		{
			return $result;
		}

        $this->entry_id = ($this->entry_id == '') ? $this->EE->input->get('entry_id') : $this->entry_id;

		if (preg_match("/[^0-9]/", $this->entry_id))
    	{
    		return $result;
    	}

		//Clear expired alerts
		$this->_clear_expired_alerts();

        if( $this->settings['disable_submit'] == "yes" && $this->entry_id != 0)
        {
            $equery = $this->EE->db->query("SELECT alert_id FROM exp_edit_alarm_ext
                                            WHERE alert_type = 'e' AND
                                            resource_id = '".$this->entry_id."' AND
                                            author_id = '".$this->EE->session->userdata['member_id']."'");

            if($equery->num_rows() == 0)
            {
                $this->_add_alert('e', $this->EE->session->userdata['member_id'], $this->entry_id);
            }
        }

		//Check for existing alerts
		$results = $this->EE->db->query("SELECT
							ea.alert_id, ea.alert_type, ea.author_id, ea.resource_id, ea.time_of_edit, m.screen_name
							FROM exp_edit_alarm_ext AS ea
							LEFT JOIN exp_members AS m ON m.member_id = ea.author_id
							WHERE alert_type = 'e'
							AND ea.author_id != '". $this->EE->session->userdata['member_id'] ."'
							AND m.screen_name IS NOT NULL
							AND resource_id = '". $this->EE->db->escape_str($this->entry_id) ."'");

        $this->add = TRUE;

  		if ($results->num_rows() > 0)
		{

            if ($results->num_rows() > 1 && $this->settings['disable_submit'] == "yes" && $this->entry_id != 0)
			{
                if($this->geteditor($this->entry_id, 'e') != $this->EE->session->userdata['member_id'])
                {
                    $this->EE->lang->loadfile('edit_alarm');
                    $this->disable = "input.submit{ display: none; }";
                    $this->disable_msg = "$('<div class=\"editAlarm\">{$this->EE->lang->line('inedit_msg')}</div>').appendTo('#publishForm');";
                }
            }

            //Display alert
			$this->EE->session->cache['ea_publish_alert'] = TRUE;
			$message = $this->_compile_alert_message('e', $results);

			$js = <<<EOFT
<!-- Edit Alarm -->
<script type="text/javascript">
$(document).ready(function() {
    $('<div class="editAlarm">{$message}</div>').prependTo('#publishForm');
    {$this->disable_msg}
});
</script>
<style type="text/css" media="screen">
{$this->settings['alert_css']}
{$this->disable}
</style>
<!-- END Edit Alarm -->
EOFT;

            $this->EE->cp->add_to_head($js);
		}

        //add alert for this author
        if ($this->add === TRUE && $this->entry_id != 0)
        {
            $this->_add_alert('e', $this->EE->session->userdata['member_id'], $this->entry_id);
        }

		return $result;
	}

	/**
	 * Delete Entry Alert
	 *
	 * Removes an alert after submission
	 *
	 */
    function delete_entry_alert($entry_id, $meta, $data)
	{
        if (! in_array('e', $this->settings['apply_to']))
		{
			return;
		}

		$this->EE->db->query("DELETE FROM exp_edit_alarm_ext
							  WHERE alert_type = 'e' AND
							  resource_id = '".$this->EE->db->escape_str($entry_id)."' AND
							  author_id = '".$this->EE->session->userdata['member_id']."'");
        return;
    }

	/**
	 * Display Template Alert
	 *
	 * Sets and displays template editing alerts
	 *
	 * @access	public
	 * @param	object	database result object for the selected template
	 * @param	string	the template id
	 * @param	string	success/fail message
	 * @return	void
	 */
	function display_template_alert($query, $template_id, $message)
	{
        //Only take action if they have enabled alerts for templates
		if (! in_array('t', $this->settings['apply_to']))
		{
            return;
		}

		//clear expired alerts
		$this->_clear_expired_alerts();

        if( $this->settings['disable_submit'] == "yes" && $template_id != 0)
        {
            $equery = $this->EE->db->query("SELECT alert_id FROM exp_edit_alarm_ext
                                            WHERE alert_type = 't' AND
                                            resource_id = '".$template_id."' AND
                                            author_id = '".$this->EE->session->userdata['member_id']."'");

            if($equery->num_rows() == 0)
            {
                $this->_add_alert('t', $this->EE->session->userdata['member_id'], $template_id);
            }
        }

        //check for existing alerts
		$query = $this->EE->db->query("SELECT
							ea.alert_id, ea.alert_type, ea.author_id, ea.resource_id, ea.time_of_edit, m.screen_name
							FROM exp_edit_alarm_ext AS ea
							LEFT JOIN exp_members AS m ON m.member_id = ea.author_id
							WHERE alert_type = 't'
							AND resource_id = '".$this->EE->db->escape_str($template_id)."'");

		$this->add = TRUE;

		if ($query->num_rows() > 0)
		{

            if ($query->num_rows() > 1 && $this->settings['disable_submit'] == "yes" && $template_id != 0)
            {

                if($this->geteditor($template_id,'t') != $this->EE->session->userdata['member_id'])
                {
                    $this->EE->lang->loadfile('edit_alarm');
                    $this->disable = "input.submit{ display: none; }";
                    $this->disable_msg = "$('<div class=\"editAlarm\">{$this->EE->lang->line('inedit_msg')}</div>').appendTo('#templateEditor');";
                }
            }

            //Display alert
            $message = $this->_compile_alert_message('t', $query);

            $js = <<<EOFT
<!-- Edit Alarm -->
<script type="text/javascript">
$(document).ready(function() {
    $('<div class="editAlarm">{$message}</div><br />').prependTo('#templateEditor');
    {$this->disable_msg}
});
</script>
<style type="text/css" media="screen">
{$this->settings['alert_css']}
{$this->disable}
</style>
<!-- END Edit Alarm -->
EOFT;

            $this->EE->cp->add_to_head($js);

		}

		//Add alert for this author
		if ($this->add === TRUE)
		{
            $this->_add_alert('t', $this->EE->session->userdata['member_id'], $template_id);


        }
	}

	/**
	 * Delete Template Alert
	 *
	 * Deletes alert for template after submission for the author
	 *
	 * @access	public
	 * @param	string	the template ID
	 * @param	string	success/fail message
	 * @return	void
	 */
	function delete_template_alert($template_id, $message)
	{

		//No point running any queries if alerts aren't active for templates
		if (! in_array('t', $this->settings['apply_to']))
		{
			return;
		}

		$this->EE->db->query("DELETE FROM exp_edit_alarm_ext
							  WHERE alert_type = 't' AND resource_id = '".$this->EE->db->escape_str($template_id)."'");

    }

	/**
	 * Display Wiki Alert
	 *
	 * Displays and sets wiki article edit alerts
	 *
	 * @access	public
	 * @param	object	the current wiki class object
	 * @param	object	the query object for the article
	 * @return	type
	 */
	function display_wiki_alert($wiki, $wquery)
	{

		//Only take action if it's an existing article and they have enabled alerts for wiki articles
		if (! in_array('w', $this->settings['apply_to']) OR $wquery->num_rows() == 0)
		{
			return $wiki->return_data;
		}

		//Clear expired alerts
		$this->_clear_expired_alerts();

		//Check for existing alerts
		$resource_id = $wquery->row_data['page_id'];

		$results = $this->EE->db->query("SELECT
							ea.alert_id, ea.alert_type, ea.author_id, ea.resource_id, ea.time_of_edit, m.screen_name
							FROM exp_edit_alarm_ext AS ea
							LEFT JOIN exp_members AS m ON m.member_id = ea.author_id
							WHERE alert_type = 'w'
							AND ea.author_id != '". $this->EE->session->userdata['member_id'] ."'
							AND m.screen_name IS NOT NULL
							AND resource_id = '".$this->EE->db->escape_str($resource_id)."'");

		// both article view and edit hooks use this method, but we only
		// want to add an alert if they are editing
		// NOTE: the $wiki->conditionals array holds STRING values, not BOOLEAN

		$this->add = ($wiki->conditionals['edit_article'] == 'TRUE') ? TRUE : FALSE;

		if ($results->num_rows() > 0)
		{
			//display alert
			$message = $this->_compile_alert_message('w', $results);

			$wiki->return_data = $wiki->_allow_if('edit_alarm', $wiki->return_data);
			$wiki->return_data = str_replace(LD.'edit_alarm_css'.RD, $this->settings['alert_css'], $wiki->return_data);
			$wiki->return_data = str_replace(LD.'edit_alarm_message'.RD, $message, $wiki->return_data);
        }

		//Add alert for this author
		if ($this->add === TRUE)
		{
			$this->_add_alert('w', $this->EE->session->userdata['member_id'], $resource_id);
		}

		return $wiki->return_data;
	}

	/**
	 * Delete Wiki Alert
	 *
	 * Deletes alert for wiki article after submission for the author
	 *
	 * @access	public
	 * @param	object	the current wiki class object
	 * @param	object	the query object for the article
	 * @return	void
	 */
	function delete_wiki_alert($wiki, $wquery)
	{

		/*
		* 	No point running any queries if alerts aren't active for templates
		*/

		if (! in_array('w', $this->settings['apply_to']))
		{
			return;
		}

		$this->EE->db->query("DELETE FROM exp_edit_alarm_ext
							WHERE alert_type = 'w' AND
							resource_id = '".$this->EE->db->escape_str($wquery->row_data['page_id'])."'");
	}

	/**
	 * Compile Alert Message
	 *
	 * Creates the alert message for us
	 *
	 * @access	public
	 * @param	string	type of alert (e/t/w)
	 * @param	object	the query for the alert(s)
	 * @return	type
	 */
	function _compile_alert_message($type, $query)
	{
		$this->EE->lang->loadfile('edit_alarm');
        switch ($type)
		{
			case 'e'	:
				$vocab = 'entry';
				break;
			case 't'	:
				$vocab = 'template';
				break;
			case 'w'	:
				$vocab = 'wiki_article';
				break;
			default		:
				$vocab = '';
		}

		$message = str_replace('%type%', $this->EE->lang->line($vocab), $this->EE->lang->line('currently_editing'));
		$members = '';
		$added = array();

		foreach ($query->result_array() as $row)
		{
			if ($row['author_id'] == $this->EE->session->userdata['member_id'])
			{
				$this->add = FALSE;
			}

			if (!in_array($row['author_id'], $added))
			{
			    $ago = number_format(($this->EE->localize->now - $row['time_of_edit']) / 60, 0);

			if ($ago == 0)
			{
				$ago = $this->EE->lang->line('less_than_one');
			}

			$minute_line = ($ago > 1) ? 'minutes_ago' : 'minute_ago';

    			$members .= '<li><b>'. $row['screen_name'].'</b> <span>('.$ago.' '.$this->EE->lang->line($minute_line).')</span></li>' ;
    			$added[] = $row['author_id'];
    		}
		}

		$message = $message . '<ul>'. $members .'</ul>';

		return $message;
	}

	/**
	 * Add Alert
	 *
	 * Adds an alert for the current resource for this author
	 *
	 * @access	public
	 * @param	string	the type (e/t/w)
	 * @param	string	the member ID
	 * @param	string	the ID of the resource
	 * @return	void
	 */
	function _add_alert($type, $member_id, $resource_id)
	{
		$data = array('alert_type' => $type,
		              'author_id' => $member_id,
		              'resource_id' => $resource_id,
		              'time_of_edit' => $this->EE->localize->now
		);
		$this->EE->db->insert('exp_edit_alarm_ext', $data);
    }

	/**
	 * Clear Expired Alerts
	 *
	 * Garbage collection for expired alerts
	 *
	 * @access	public
	 * @return	void
	 */
	function _clear_expired_alerts()
	{
		$expired = $this->EE->localize->now - ($this->settings['expire'] * 60); // expire setting is in minutes
        $this->EE->db->query("DELETE FROM exp_edit_alarm_ext WHERE
                                time_of_edit < '".$this->EE->db->escape_str($expired)."'");
    }

	/**
	 * Settings
	 *
	 * Set Extension Settings
	 *
	 * @access	public
	 * @return	array
	 */
	function settings()
	{
		$settings = array();
		$apply_to = array(
							'e'	=> $this->EE->lang->line('channel_entries'),
							't' => $this->EE->lang->line('templates'),
							'w'	=> $this->EE->lang->line('wiki_articles')
						);

		$settings['apply_to']		= array('ms', $apply_to, '');
		$settings['expire']			= 10;
        $settings['disable_submit']  = array('r', array('yes' => "yes", 'no' => "no"), 'no');
		$settings['alert_css']		= array('t', '', '');

		return $settings;
	}

	/**
	 * Activate Extension
	 *
	 * Installs the extension
	 *
	 * @access	public
	 * @return	void
	 */
	function activate_extension()
	{
        // Delete any existing rows and drop any existing tables
        $this->EE->db->query("DELETE FROM exp_extensions WHERE class = '" . __CLASS__ . "'");
        $this->EE->db->query("DROP TABLE IF EXISTS `exp_edit_alarm_ext`");

		$this->EE->db->insert('exp_extensions',
				array(
				'extension_id'	=> '',
				'class'			=> __CLASS__,
				'method'		=> "display_entry_alert",
				'hook'			=> "publish_form_entry_data",
				'settings'		=> "",
				'priority'		=> 10,
				'version'		=> $this->version,
				'enabled'		=> "y"
				)
			);

        $this->EE->db->insert('exp_extensions',
                array(
                'extension_id'    => '',
                'class'           => __CLASS__,
                'method'          => "delete_entry_alert",
                'hook'            => "entry_submission_absolute_end",
                'settings'        => "",
                'priority'        => 10,
                'version'         => $this->version,
                'enabled'         => "y"
                )
        );

		$this->EE->db->insert('exp_extensions',
				array(
				'extension_id'	=> '',
				'class'			=> __CLASS__,
				'method'		=> "display_template_alert",
				'hook'			=> "edit_template_start",
				'settings'		=> "",
				'priority'		=> 10,
				'version'		=> $this->version,
				'enabled'		=> "y"
				)
		);

		$this->EE->db->insert('exp_extensions',
				array(
				'extension_id'	=> '',
				'class'			=> __CLASS__,
				'method'		=> "delete_template_alert",
				'hook'			=> "update_template_end",
				'settings'		=> "",
				'priority'		=> 10,
				'version'		=> $this->version,
				'enabled'		=> "y"
				)
		);

		$this->EE->db->insert('exp_extensions',
				array(
				'extension_id'	=> '',
				'class'			=> __CLASS__,
				'method'		=> "display_wiki_alert",
				'hook'			=> "edit_wiki_article_form_end",
				'settings'		=> "",
				'priority'		=> 10,
				'version'		=> $this->version,
				'enabled'		=> "y"
				)
		);

		$this->EE->db->insert('exp_extensions',
				array(
				'extension_id'	=> '',
				'class'			=> __CLASS__,
				'method'		=> "display_wiki_alert",
				'hook'			=> "wiki_article_end",
				'settings'		=> "",
				'priority'		=> 10,
				'version'		=> $this->version,
				'enabled'		=> "y"
				)
		);

		$this->EE->db->insert('exp_extensions',
				array(
				'extension_id'	=> '',
				'class'			=> __CLASS__,
				'method'		=> "delete_wiki_alert",
				'hook'			=> "edit_wiki_article_end",
				'settings'		=> "",
				'priority'		=> 10,
				'version'		=> $this->version,
				'enabled'		=> "y"
				)
		);

		$this->EE->db->query("CREATE TABLE `exp_edit_alarm_ext`(
			  		`alert_id` int(5) unsigned NOT NULL auto_increment,
					`alert_type` char(1) NOT NULL,
			  		`author_id` int(10) unsigned NOT NULL,
			  		`resource_id` int(10) unsigned NOT NULL,
			  		`time_of_edit` int(10) unsigned NOT NULL,
			  		PRIMARY KEY  (`alert_id`),
					KEY `alert_type` (`alert_type`),
			  		KEY `author_id` (`author_id`),
					KEY `resource_id` (`resource_id`)
					)"
				);

		$defaults = array();
		$defaults['apply_to'] = array('e','t','w');
        $defaults['expire'] = 10;
        $defaults['disable_submit'] = 'no';
		$defaults['alert_css'] = <<<EOFT
.editAlarm {
    color: inherit;
    font-size: 12px;
    line-height: 1.5em;
    margin: 0;
    padding: 7px 10px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
    color: inherit;

    background-color: #fffcbf;
    background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, color-stop(0%, rgba(255, 255, 255, 0.3)), color-stop(100%, rgba(255, 255, 255, 0)));
    background-image: -moz-linear-gradient(top, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 100%);
    background-image: linear-gradient(top, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 100%);
}

.editAlarm ul {
    margin: 0;
    padding-left: 1.5em;
    list-style: disc !important;
}

.editAlarm span {
    font-size: small;
    color: rgba(0, 0, 0, 0.5);
}
EOFT;

		$settings = array('settings' => serialize($defaults));

		$this->EE->db->update('exp_extensions', $settings, "class = '" . __CLASS__ . "'");
	}


    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return     mixed    void on update / false if none
     */
    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }

        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->update(
                    'extensions',
                    array('version' => $this->version)
        );

    }

    /**
     * Disable Extension
     *
     * Uninstalls the extension
     *
     * @access    public
     * @return    void
     */
	function disable_extension()
	{
		$this->EE->db->query("DELETE FROM exp_extensions WHERE class = '" . __CLASS__ . "'");
		$this->EE->db->query("DROP TABLE IF EXISTS `exp_edit_alarm_ext`");
	}

	/**
	 * Get Editor
	 *
	 * Query Results for editors
	 *
	 * @param   int resource id
	 * @param   string alert type (e,t, or w)
	 * @return
	 */
    function geteditor($entry, $type)
    {
        $query = $this->EE->db->query("SELECT author_id FROM exp_edit_alarm_ext
                                       WHERE alert_type = '".$type."' AND
                                       resource_id = " . $entry . "
                                       ORDER BY time_of_edit ASC LIMIT 1");
        return $query->row('author_id');
    }

	/**
	 * Alert Check
	 *
	 * Checks if alert exists
	 *
	 * @param   int member id
	 * @param   int resource id
	 * @param   array results array
	 * @return bool
	 */
    function alertcheck($memberId, $resourceId, $resultArray = array())
    {
        foreach($resultArray as $object)
        {
            echo $object->author_id . '|' . $object->resource_id . '<br />';
            if( $object->author_id = $memberId && $object->resource_id = $resourceId)
            {
                $exist = TRUE;
            }
        }

        if(isset($exist))
        {
            return TRUE;
        }else{
            return FALSE;
        }

    }



}
// END Edit Alarm Class
