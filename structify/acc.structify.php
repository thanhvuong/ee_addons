<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Structify Accessory
 *
 * @package			Structify
 * @subpackage		Accessories
 * @category		Accessories
 * @author			Thanh Vuong
 * @link			http://www.thanhvuong.com/dev/
 */

class Structify_acc {

	var $name			= 'Structify';
	var $id				= 'structify';
	var $version		= '1.3';
	var $description	= 'An Accessory for enhancing Structure\'s tab view on the publish form.';
	var $sections		= array();
	var $site_url;
	var $site_id;
	var $site_pages;
	var $is_new_page;
	var $eid;
	var $page_url;


	/**
	 * Constructor
	 */
	function Structify_acc()
	{		

		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
		$this->site_pages = $this->EE->config->item('site_pages'); 

		$this->site_url = rtrim($this->site_pages[$this->site_id]['url'], '/');
		$this->eid = $this->EE->input->get('entry_id');
		$this->is_new_page = ($this->eid && isset($this->site_pages[$this->site_id]['uris'][$this->eid])) ? FALSE : TRUE;
		$this->page_url = (!$this->is_new_page) ? $this->site_url . $this->site_pages[$this->site_id]['uris'][$this->eid] : FALSE;

	}

	// --------------------------------------------------------------------

	/**
	 * Update
	 */
	function update()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	function set_sections()
	{

		$this->sections['Structify'] = '
		<p>When using the publish form, the structure tab displays a drop downbox by default. Structify gives you a couple enhancements which serves as visual aids.<br /><br /><strong>Copy URL</strong><br />This will copy the url title from the publish tab to the structure page url field.<br /><br /><strong>Easy Picker</strong><br /> "Easy Picker" will give you a pop up dialog with a list of entries where you can choose the parent entry for this current entry.<br /><br /><strong>Expand Dropdown</strong><br /> Clicking "Expand Dropdown" will convert the drop down to a list box for better viewing and selection.<br /><br />If you have a very large list, Easy Picker allows you to use Ctl+F or Cmd-F to find what you are looking for. I have also added the feature where it would check if users are trying to set the parent to itself and if so, it will disable the submit button.</p>
		<style type="text/css">
			select#structure__parent_id { display: inline; }
			.dialogbox{ background: #ecf1f4; }
			#thanhdiv{ margin-left: 0.75em; margin-bottom: 5px; padding: 5px;}
			#thanhdiv button { margin-right: 5px; background: #3a4850; padding: 5px 9px; color:	#fff; border: 0; }
			#thanhdiv button:hover { cursor: pointer; }
			#colDropdown{ display: none; }
			.menulist{ -webkit-appearance: menulist!important; }
			.listbox{ -webkit-appearance: listbox!important; }
			#epData small{ text-decoration: underline; }
			ul#easyPicker{ width: 100%;}
			ul#easyPicker li{ /* background: #D0D7DF; */ line-height: 30px; margin-bottom: 5px; display: block; }
			ul#easyPicker li:hover{ background: #e11842; color: #fff; cursor: pointer;}
			ul#easyPicker li:hover span{ color: #fff; }
			ul#easyPicker li span{ font-weight: 700; color: #000; }
			#structwarning,#parentwarning{ color: red; display: block!important; padding: 20px 0px; }
		}
		</style>
		<script type="text/javascript">
		
		$(document).ready(function() 
		{
			var full_url = "' . (($this->page_url) ? $this->page_url : '') . '";
			var previous_url = $("input#structure__uri").val();

			var param = "entry_id";
			var entryId = decodeURIComponent((new RegExp("[?|&]" + param + "=" + "([^&;]+?)(&|#|;|$)").exec(location.search)||[,""])[1].replace(/\+/g, "%20"))||null;

			var warningText = "Notice: You did not select a parent entry in the Structure tab. The only pages that should not have a parent are landing pages living at the \"top level\" of the website. Are you sure you want to submit without a parent entry? ";

			var parentWarning = "Warning: You have set the parent entry to itself. This will not end well. The submit button is now disabled. You must change the  parent entry before the submit button is re-enabled.";


			if(full_url !== "")	{
				var urlify = "<p id=\"fullurlify\">Current URL (provided by Structify): <a href=\"" + full_url + "\" target=\"_blank\">" + full_url + "</a></p>";

					$("#structure__uri").after(urlify);
			}

			$("input#structure__uri").on("keyup",function() {
			    var current_url = $("input#structure__uri").val();
			    if(current_url == previous_url) {
					$("#fullurlify").show();
			    }else{
			    	$("#fullurlify").hide();
			    }
			});


      		if ($("#structure__parent_id option[value=0]").attr("selected")) {
      			$("#publish_submit_buttons").prepend("<li id=\"structwarning\">" + warningText + "</li>");
      		}

			if ($("select#structure__parent_id").length > 0) {

				var button_group = "<div id=\"thanhdiv\"><button id=\"curi\" type=\"button\">Copy URL</button><button id=\"epbutton\" type=\"button\">Easy Picker</button><button id=\"expandDropdown\" type=\"button\">Expand Dropdown</button><button id=\"colDropdown\" type=\"button\">Collapse Dropdown</button><button id=\"epAbout\" type=\"button\">?</button></div>";

				$("#sub_hold_field_structure__parent_id").prepend(button_group);  

				$("#curi").click(function(e) {
					$("#structure__uri").val($("#url_title").val());
					e.preventDefault();
				});

				$("#structure__parent_id").change(function() {
          				if ($("#structure__parent_id option:selected").val() != 0) {
            					$("#structwarning").hide();
          				}else{
            					$("#structwarning").show();
					}
        			});
				
				$("#expandDropdown").click(function(e) {
					$("select#structure__parent_id").attr("max-height", "341px").attr("size", 20).width(600);
					$("#expandDropdown").hide();
					$("#colDropdown").show();
					e.preventDefault();
				});
				
				$("#colDropdown").click(function(e) {
					$("select#structure__parent_id").removeAttr("size").removeAttr("width").removeAttr("max-height").removeClass("listbox");
					$("#colDropdown").hide();
					$("#expandDropdown").show();
					e.preventDefault();
				});

				var optionValues = [];
				var optionTexts = [];
				var selText = "";
				var epOutput = "<ul id=\"easyPicker\">";
				var slength = $("select#structure__parent_id > option").length;
				
				$("select#structure__parent_id > option").each(function() {
					selText = ($(this).text().substring(0, 1) == "-") ? $(this).text().replace(/-/g, "&mdash;") : "<span>" + $(this).text().replace(/-/g, "&mdash;") + "</span>";
					epOutput += "<li rel=\"" + $(this).val() + "\">" + selText + " (id: " + $(this).val() + ")</li>";
				});

				
				epOutput += "</ul>";

				var $epStuff = $("<div id=\"epData\"></div>").html("<small>Tip: If this list is very long, please use \"Find\" via Ctl+F or Cmd+F</small><br /><br />" + epOutput).dialog({ modal: true, resizable: true, height:550, autoOpen: false, width: 700, title: "Please select the parent to this entry"});
				var $epAbout = $("<div id=\"epData\"></div>").html("When using the publish form, the structure tab displays a drop downbox by default. Structify gives you a couple enhancements which serve as visual aids.<br /><br /><strong>Copy URL</strong><br />This will copy the url title from the publish tab to the structure page url field.<br /><br /><strong>Easy Picker</strong> \"Easy Picker\" will give you a pop up dialog with a list of entries where you can choose the parent entry for this current entry.<br /><br /><strong>Expand Dropdown</strong> Clicking \"Expand Dropdown\" will convert the drop down to a list box for better viewing and selection.<br /><br />If you have a very large list, Easy Picker allows you to use Ctl+F or Cmd-F to find what you are looking for. I have also added the feature where it would check if users are trying to set the parent to itself and if so, it will disable the submit button.").dialog({ modal: true, resizable: false, autoOpen: false, width: 500, title: "Structify v1.2 by Thanh Vuong"});
				
				$("#epAbout").click(function(e) {
					$epAbout.dialog("open");
					$(".ui-dialog").addClass("dialogbox");
					e.preventDefault();
				});
				
				$("#epbutton").click(function(e) {
					$epStuff.dialog("open");
					$(".ui-dialog").addClass("dialogbox");
					e.preventDefault();
				});

				$("ul#easyPicker > li").live("click", function() {
					$("select#structure__parent_id").val($(this).attr("rel")).change();
					$epStuff.dialog("close");
				});
				
				$(".ui-widget-overlay").live("click", function() { $epStuff.dialog("close"); $epAbout.dialog("close"); } );
				
			}
			
		});
		
		
		</script>';
	}

}
// END CLASS

/* End of file acc.structify.php */
/* Location: ./system/expressionengine/third_party/structify/acc.structify.php */