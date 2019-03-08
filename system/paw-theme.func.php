<?php
/*
 |  paw.Designer - A advanced Theme Environment for Bludit
 |  @file       ./paw-theme.func.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    /*
     |  API :: CONFIGURE AN OPTION
     |  @since   0.1.0
     |
     |  @param  string  The unique option KEY as STRING.
     |  @param  array   The additional option interface configuration.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function pd_configure_option($key, $config){
        global $PawDesignerTheme;
        return $PawDesignerTheme->configureOption($key, $config);
    }

    /*
     |  API :: CONFIGURE A MENU
     |  @since  0.1.0
     |
     |  @param  string  The unique menu position as STRING.
     |  @param  array   The additional menu interface configuration.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function pd_configure_menu($position, $config){
        global $PawDesignerTheme;
        return $PawDesignerTheme->configureMenu($position, $config);
    }

    /*
     |  HELPER :: SELECTED
     |  @since   0.1.0
     |
     |  @param  multi   The option value.
     |  @param  multi   The value to compare, multiple as ARRAY.
     |  @param  bool    TRUE to print 'selected="selected"' false to return it.
     |
     |  @return string  The 'selected="selected"' if selected an empty string otherwise.
     */
    function pd_selected($value, $compare = true, $print = true){
        if(is_array($compare)){
            $selected = in_array($value, $compare)? "selected=\"selected\"": "";
        } else {
            $selected = ($value == $compare)? "selected=\"selected\"": "";
        }
        if(!$print){
            return $selected;
        }
        print($selected);
    }

    /*
     |  HELPER :: CHECKED
     |  @since   0.1.0
     |
     |  @param  multi   The option value.
     |  @param  multi   The value to compare, multiple as ARRAY.
     |  @param  bool    TRUE to print 'checked="checked"' false to return it.
     |
     |  @return string  The 'checked="checked"' if selected an empty string otherwise.
     */
    function pd_checked($value, $compare = true, $print = true){
        if(is_array($compare)){
            $checked = in_array($value, $compare)? "checked=\"checked\"": "";
        } else {
            $checked = ($value == $compare)? "checked=\"checked\"": "";
        }
        if(!$print){
            return $checked;
        }
        print($checked);
    }
