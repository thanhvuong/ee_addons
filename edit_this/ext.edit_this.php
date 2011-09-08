<?php
/**
 * Edit This for EE 2.x
 *
 * This extension adds a little 'one click away' edit link next to your channel entries your website.
 * For EE 1.x, use "Editor" originally created by Brandon Kelly at http://brandon-kelly.com/editor
 *
 * @package   Edit This
 * @author    Thanh Vuong <thanh@vuongs.org>
 * @link      http://www.thanhvuong.com/dev/
 * @copyright Copyright (c) 2010 Thanh Vuong
 * @license   http://creativecommons.org/licenses/by-sa/3.0/   Attribution-Share Alike 3.0 Unported
 */

 class Edit_this_ext{
     /**
     * Extension Settings
     *
     * @var array
     */
    var $settings = array();
    /**
     * Extension Name
     *
     * @var string
     */
    var $name = 'Edit This';
    /**
     * Extension Class Name
     *
     * @var string
     */
    var $class_name = 'Edit_this';
    /**
     * Extension Version
     *
     * @var string
     */
    var $version = '1.0';
    /**
     * Extension Description
     *
     * @var string
     */
    var $description = 'Access the Edit form right from your site';
    /**
     * Extension Settings Exist
     *
     * If set to 'y', a settings page will be shown in the Extensions Manager
     *
     * @var string
     */
    var $settings_exist = 'y';
    /**
     * Documentation URL
     *
     * @var string
     */
    var $docs_url = 'http://www.thanhvuong.com/dev/';
    // var $cache;

    /**
     * Constructor
     *
     * @param     mixed    Settings array or empty string if none exist.
     */
    function Edit_this_ext($settings = array())
    {
        $this->EE =& get_instance();
        $this->EE->lang->loadfile('edit_this');
        $this->settings = $this->_get_settings();
    }

    /**
    * Settings
    *
    */
    function settings()
    {
        $settings = array();

        $settings['cp_url'] = isset($this->cp_url) ? $this->cp_url : '';
        $settings['cp_imgurl'] = isset($this->cp_imgurl) ? $this->cp_imgurl : '';

        if (! isset($this->EE->session->cache['editthis']))
        {
            $this->EE->session->cache['editthis'] = array();
        }
        $this->cache =& $this->EE->session->cache['editthis'];

        return $settings;
    }

    /**
     * Activate Extension
     *
     * Resets all Edit This exp_extensions rows
     *
     * @since version 1.0.0
     */
    function activate_extension(){
        // Delete any existing Edit This rows
        $this->EE->db->query("DELETE FROM exp_extensions WHERE class = '". __CLASS__ ."'");

        $ext_template = array(
          'class' => __CLASS__,
          'method' => 'modify_template',
          'hook' => 'channel_entries_tagdata',
          'settings' => serialize($this->settings()),
          'priority' => 10,
          'version' => $this->version,
          'enabled' => 'y'

        );

        // Inserts Edit This row
        $this->EE->db->insert('exp_extensions', $ext_template);

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
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension()
    {
        // Delete records
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('exp_extensions');
    }

    /**
    * Get Settings
    *
    */
    private function _get_settings($force_refresh = FALSE)
    {
        // assume there are no settings
        $settings = FALSE;
        $this->EE->load->helper('string');

        // Get the settings for the extension
        if(isset($this->cache['settings']) === FALSE || $force_refresh === TRUE)
        {
            // check the db for extension settings
            $this->EE->db->select('settings');
            $this->EE->db->where('enabled', 'y');
            $this->EE->db->where('class', __CLASS__);
            $this->EE->db->limit('1');
            $query = $this->EE->db->get('exp_extensions');

            // if there is a row and the row has settings
            if ($query->num_rows() > 0 && $query->row('settings') != '')
            {
                // save them to the cache
                $this->cache['settings'] = strip_slashes(unserialize($query->row('settings')));
            }
        }

    }

    /**
     * Modify Template
     *
     * Adds the edit link/image
     *
     * @param  string   $tagdata   The Weblog Entries tag data
     * @param  array    $row       Array of data for the current entry
     * @param  object   $weblog    The current Weblog object including all data relating to categories and custom fields
     * @return string              Modified $tagdata
     */
    function modify_template($tagdata, $row=array(), &$weblog)
    {
        // return tagdata if user can't edit entries
        if ( ! (isset($this->EE->session->userdata) && isset($this->EE->session->userdata['can_access_edit']) AND $this->EE->session->userdata['can_access_edit'] == 'y'))
        {
            return $tagdata;
        }

        global $EDITTHISENTRIES, $EDITTHISBASEURL;

        // Define $EDITTHISENTRIES to keep track of which
        // entries we've already created buttons for
        if ( ! isset($EDITTHISENTRIES))
        {
            $EDITTHISENTRIES = array();
            //$entriesArr = array();

            if ( ! ($this->settings && isset($this->cache['settings']['cp_url'])))
            {
                $this->settings = $this->cache['settings'];
            }

            $EDITTHISBASEURL = ($this->cache['settings']['cp_url'] ? $this->cache['settings']['cp_url'] : $this->EE->config->default_ini['cp_url'])
                         . '?S='.$this->EE->session->userdata['session_id'];

            // Add Edit This styles
            if( ! $this->settings['cp_imgurl'])
            {
                // if there is no image url set in the control panel, use this style
                $tagdata = "\n" . '<style type="text/css"> '
                         . "\n" . '.editor-button { position:absolute; z-index: 999!important; } '
                         . "\n" . '.editor-button a { display:block; top:0; margin-left:-30px; width:30px; height:14px; background: #ff0000; overflow:hidden; font: 10px arial; color: #fff!important; line-height: 14px; text-decoration: none!important; text-align: center;} '
                         . "\n" . '.editor-button a:hover { opacity:1; }'
                         . "\n" . '</style>'
                         . "\n" . $tagdata;
            }else{
                // if there's an image url set in the control panel, then use this style
                $tagdata = "\n" . '<style type="text/css"> '
                         . "\n" . '.editor-button { position:absolute; z-index: 999!important;  } '
                         . "\n" . '.editor-button a { display:block; top:0; margin-left:-16px; width:16px; height:12px; background:url('.$this->cache['settings']['cp_imgurl'].') no-repeat 0 0; opacity:1.0; text-indent:-9999em; overflow:hidden; } '
                         . "\n" . '.editor-button a:hover { opacity:1; }'
                         . "\n" . '</style>'
                         . "\n" . $tagdata;
            }

        }

        if (isset($row['entry_id']) && is_numeric($row['entry_id']) && ( ! in_array($row['entry_id'], $EDITTHISENTRIES)))
        {
            // Add button
            $title =  $this->EE->lang->line('edit')." &ldquo;{$row['title']}&rdquo;";

            if( ! $this->settings['cp_imgurl'])
            {
                // if there's no image url set in the control panel, then insert this
                $tagdata = "\n" . '<div class="editor-button">'
                         . "\n" . '<a href="'.$EDITTHISBASEURL.'&amp;D=cp&amp;C=content_publish&amp;M=entry_form&amp;channel_id='.$row['channel_id'].'&amp;entry_id='.$row['entry_id'].'" title="'.$title.'" target="_blank">'.$this->EE->lang->line('edit').' &raquo;</a>'
                         . "\n" . '</div>'
                         . "\n" . $tagdata;
            }else{
                // if there is an image url set in the control panel, then insert this
                $tagdata = "\n" . '<div class="editor-button">'
                         . "\n" . '<a href="'.$EDITTHISBASEURL.'&amp;D=cp&amp;C=content_publish&amp;M=entry_form&amp;channel_id='.$row['channel_id'].'&amp;entry_id='.$row['entry_id'].'" title="'.$title.'" target="_blank"></a>'
                         . "\n" . '</div>'
                         . "\n" . $tagdata;
            }

            // Add entry id to $EDITTHISENTRIES array
            $EDITTHISENTRIES[] = $row['entry_id'];
        }
        return $tagdata;
    }


 } // END CLASS

/* End of file ext.edit_this.php */
/* Location: ./system/expressionengine/third_party/edit_this/ext.edit_this.php */