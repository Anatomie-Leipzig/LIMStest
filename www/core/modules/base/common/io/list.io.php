<?php
/**
 * @package base
 * @version 0.4.0.0
 * @author Roman Konertz <konertz@open-lims.org>
 * @copyright (c) 2008-2011 by Roman Konertz
 * @license GPLv3
 * 
 * This file is part of Open-LIMS
 * Available at http://www.open-lims.org
 * 
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation;
 * version 3 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, see <http://www.gnu.org/licenses/>.
 */

/**
 * List IO Class
 * @package base
 */
class List_IO
{	
	private $entries_per_page;
	
	private $display_header;
	private $display_footer;
	
	private $ajax_handler;
	private $ajax_run;
	private $ajax_count_run;
	private $argument_array;
	private $css_main_id;
	
	private $rows = array();
	
	/**
	 * @param string $list_name A system wide unique list name
	 * @param string $ajax_handler Path to AJAX Handler
	 * @param string $ajax_run Function which returns the list
	 * @param string $ajax_count_run Function which returns the number of entries
	 * @param array $argument_array Array with specific arguments
	 * @param string $css_main_id CSS ID which will be used for the <tbody> element in the table
	 * @param integer $entries_per_page
	 * @param bool $display_header
	 * @param bool $display_footer
	 */
    function __construct($list_name, $ajax_handler, $ajax_run, $ajax_count_run, $argument_array, $css_main_id, $entries_per_page = 20, $display_header = true, $display_footer = true)
    {
    	if ($ajax_handler and $ajax_run and $ajax_count_run)
    	{
    		$this->entries_per_page = $entries_per_page;
    		
    		$this->ajax_handler = $ajax_handler;
    		$this->ajax_run = $ajax_run;
    		$this->ajax_count_run = $ajax_count_run;
    		
    		$this->argument_array = $argument_array;
    		$this->css_main_id = $css_main_id;
    		
    		$this->display_header = $display_header;
    		$this->display_footer = $display_footer;
    	}
    }
    
 	public function add_row($title, $address, $sortable, $width, $row_css_id = null, $hideable = true)
    {
    	if ($address)
    	{
    		$row_array = array();
    		$row_array[0] = $title;
    		$row_array[1] = $address;
    		
    		if ($width != null)
    		{
    			$row_array[2] = $width;
    		}
    		else
    		{
    			$row_array[2] = null;
    		}
    			    		
    		if ($sortable == true)
    		{
    			$row_array[3] = true;
    		}
    		else
    		{
    			$row_array[3] = false;
    		}
    		
    		if ($row_css_id)
    		{
    			$row_array[4] = $row_css_id;
    		}
    		else
    		{
    			$row_array[4] = null;
    		} 
    		
    		if ($hideable == true)
    		{
    			$row_array[5] = true;
    		}
    		else
    		{
    			$row_array[5] = false;
    		} 
    		
    		array_push($this->rows, $row_array);
    	}
    	else
    	{
    		return false;
    	}
    }
    
    public function get_list()
    {
		$page = 1;
    	
    	if ($GLOBALS['autoload_prefix'])
		{
			$path_prefix = $GLOBALS['autoload_prefix'];
		}
		else
		{
			$path_prefix = "";
		}
	
    	$template = new Template($path_prefix."template/base/list/list.html");	
    		
    	if ($this->display_header == true)
		{
			$template->set_var("display_header", true);
		}
		else
		{
			$template->set_var("display_header", false);
		}
    	
		$head .= "<table class='OverviewTable'><thead><tr>";
		
		foreach ($this->rows as $key => $value)
		{
			if ($value[3] == true)
			{
				$paramquery = $_GET;
				unset($paramquery[sortvalue]);
				unset($paramquery[sortmethod]);
				$params = http_build_query($paramquery, '', '&#38;');
				
				if ($value[2] != null)
				{
					$head .= "<th width='".$value[2]."'  style='width:".$value[2].";' class='".$this->css_main_id."Row' id='".$this->css_main_id."Row".$value[1]."'>" .
									"<a href='#'>".$value[0]."</a>" .
									"&nbsp;<a href='#'>" .
											"<img src='images/nosort.png' alt='' border='0' />" .
									"</a>" .
									"</th>";
				}
				else
				{
					
					$head .= "<th class='".$this->css_main_id."Row' id='".$this->css_main_id."Row".$value[1]."'>" .
									"<a href='#'>".$value[0]."</a>" .
									"&nbsp;<a href='#'>" .
											"<img src='images/nosort.png' alt='' border='0' />" .
									"</a>" .
									"</th>";
				}
			}
			else
			{
				if ($value[2] != null)
				{
					$head .= "<th width='".$value[2]."' style='width:".$value[2].";'>".$value[0]."</th>";
				}
				else
				{
					$head .= "<th>".$value[0]."</th>";
				}
			}
				
		}
		
		$head .= "</tr></thead>";	
		
    	$template->set_var("top_left_text", "");
    	$template->set_var("top_right_text", "");
    	
    	$template->set_var("head", $head);
		    	
    	$template->set_var("ajax_handler", $this->ajax_handler);
    	$template->set_var("ajax_run", $this->ajax_run);
    	$template->set_var("ajax_count_run", $this->ajax_count_run);
    	$template->set_var("argument_array", json_encode($this->argument_array));
    	$template->set_var("get_array", serialize($_GET));
    	$template->set_Var("css_main_id", $this->css_main_id);
    	$template->set_var("entries_per_page", $this->entries_per_page);
    	$template->set_var("row_array", json_encode($this->rows));

    	$template->set_var("list_div", "<tbody id='".$this->css_main_id."'></tbody></table>");
    	
    	if ($this->display_footer == true)
		{
			$pagebar = "<div class='ResultNextPageBar' id='".$this->css_main_id."PageBar'></div>";	
			$template->set_var("pagebar", $pagebar);
		}			
		else
		{
			$template->set_var("pagebar", "");
		}
    	
		return $template->get_string();
    }
}