<?php
/*
 |  paw.Designer - A advanced Theme Engine for Bludit
 |  @file       ./system/paw-theme.class.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class PawTheme{
        /*
         |  GLOBAL :: CUSTOM METADATA
         |      "paw.designer"          The minimum version of the plugin, which is supported
         |      "paw.designer-file"     The paw.Designer theme configuration file
         |      "paw.designer-force"    Forces to load the "paw.designer.func.php" file from the theme
         */
        static private $meta = array(
            "paw.designer"          => "",
            "paw.designer-file"     => "theme.php",
            "paw.designer-force"    => false
        );

        /*
         |  INSTANCE :: THEME PATH
         */
        private $path = "";

        /*
         |  INSTANCE :: THEME ID / DIRECTORY
         */
        private $theme = false;

        /*
         |  INSTANCE :: THEME METADATA
         */
        private $data = array();

        /*
         |  INSTANCE :: THEME MENU ARRAY
         */
        private $menus = array();

        /*
         |  INSTANCE :: THEME OPTIONS ARRAY
         */
        private $options = array();


        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct($theme_dir, $backend = false){
            global $PawDesignerTheme;

            if(!empty($theme_dir) && Sanitize::pathFile(PATH_THEMES . $theme_dir)){
                $this->path = PATH_THEMES . $theme_dir;
                $this->theme = $theme_dir;

                $PawDesignerTheme = $this;
                if(($theme = $this->loadTheme($theme_dir)) !== false){
                    $this->data = $theme;
                    if($backend){
                        $this->loadConfig();
                    }
                }
            }
        }

        /*
         |  INTERNAL :: LOAD THEME DATA
         |  @since  0.1.0
         */
        private function loadTheme($theme){
            global $site;

            // Check Language File
            $langFile = $this->path . DS . "languages" . DS . $site->language() . ".json";
            if(!Sanitize::pathFile($langFile)){
                $langFile = $this->path . DS . "languages" . DS . DEFAULT_LANGUAGE_FILE;
            }
            if(!Sanitize::pathFile($langFile)){
                return false;
            }

            // Load Language File
            $data = file_get_contents($langFile);
            $data = json_decode($data, true);
            if(empty($data) || !isset($data["theme-data"])){
                return false;
            }
            $data = $data["theme-data"];
            $data["dirname"] = basename($this->path);

            // Load Meta Data
            if(!Sanitize::pathFile($this->path . DS . "metadata.json")){
                return false;
            }
            $meta = file_get_contents($this->path . DS . "metadata.json");
            $meta = json_decode($meta, true);
            if(empty($meta) || !isset($meta["paw.designer"])){
                return false;
            }

            // Meta and Load Theme Configuration
            $data = array_merge($data, self::$meta, $meta);
            return $data;
        }

        /*
         |  INTERNAL :: LOAD THEME CONFIGURATION
         |  @since  0.1.0
         */
        public function loadConfig(){
            global $PawDesignerPlugin;

            // Load Theme Config File
            if(!file_exists($this->path . DS . $this->data["paw.designer-file"])){
                if(!file_exists($this->path . DS . "theme.php")){
                    return false;
                }
                $this->data["paw.designer-file"] = "theme.php";
            }
            if(!$this->getData("paw.designer-force")){
                require_once(PAW_DESIGNER_PATH . "system/paw-designer.func.php");
            }
            include_once($this->path . DS . $this->data["paw.designer-file"]);

            // Load DataBase Config
            if(($data = $PawDesignerPlugin->getTheme($this->theme)) !== false){
                if(isset($data["options"])){
                    foreach($data["options"] AS $key => $value){
                        $this->options[$key]["value"] = $value;
                    }
                }
                if(isset($data["menus"])){
                    foreach($data["menus"] AS $key => $value){
                        $this->menus[$key]["value"] = $value;
                    }
                }
            }
            return true;
        }

        /*
         |  HELPER :: IS THEME
         |  @since  0.1.0
         */
        public function isTheme(){
            return (is_array($this->data) && isset($this->data["paw.designer"]));
        }

        /*
         |  HELPER :: GET THEME
         |  @since  0.1.0
         */
        public function getTheme(){
            return $this->theme;
        }

        /*
         |  HELPER :: GET THEME
         |  @since  0.1.0
         */
        public function getPath(){
            return $this->path;
        }

        /*
         |  HELPER :: GET DATA
         |  @since  0.1.0
         */
        public function getData($key = NULL, $default = ""){
            if(empty($key)){
                return $this->data;
            }
            return isset($this->data[$key])? $this->data[$key]: $default;
        }

        /*
         |  HELPER :: GET MENUs
         |  @since  0.1.0
         */
        public function getMenus(){
            return $this->menus;
        }

        /*
         |  HELPER :: GET OPTIONs
         |  @since  0.1.0
         */
        public function getOptions($value = false){
            if($value === true){
                $return = array();
                foreach($this->options AS $key => $data){
                    $return[$key] = $data["value"];
                }
                return $return;
            }
            return $this->options;
        }

        /*
         |  API :: SET AN OPTION
         |  @since  0.1.0
         |
         |  @param  string  The unique option KEY as STRING.
         |  @param  multi   The default option VALUE, gets overwritten by the user.
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        public function setOption($key, $default = NULL){
            if(array_key_exists($key, $this->options) || !is_string($key)){
                return false;
            }

            // Determine Option Type
            if(is_bool($default)){
                $data = array(
                    "type"          => "onoff",
                    "title"         => $key
                );
            } else if(is_int($default) || is_float($default)){
                $data = array(
                    "type"          => "number",
                    "title"         => $key,
                    "float"         => is_float($default)
                );
            } else if(is_string($default)){
                if((strlen($default) == 4 || strlen($default) == 7) && $default[0] === "#"){
                    $data = array(
                        "type"          => "color",
                        "title"         => $key
                    );
                } else {
                    $data = array(
                        "type"          => "text",
                        "title"         => $key
                    );
                }
            } else {
                $data = array(
                    "type"  => "unknown",
                    "title" => $key
                );
            }

            // Add Option
            $this->options[$key] = array_merge($data, array(
                "description"   => pd__("Unconfigured Option!"),
                "value"         => $default
            ));
            return true;
        }

        /*
         |  API :: CONFIGURE AN OPTION
         |  @since  0.1.0
         |
         |  @param  string  The unique option KEY as STRING.
         |  @param  array   The additional option interface configuration.
         |                  GLOBAL CONFIG
         |                      "type"          The options type (required); Available types:
         |                                      "text", "password", "number", "color", "checkbox",
         |                                      "radio", "select", "image", "onoff", "pages"
         |                      "title"         A short title (required)
         |                      "description"   A descriptive description
         |                      "value"         The default value / selection
         |                      "placeholder"   The placeholder value (doesn't work on 'checkbox',
         |                                      'radio' and 'select' fields)!
         |
         |                  TYPE SPECIFIC "type => number"
         |                      "min"           The minimum required number
         |                      "max"           The maximum allowed number
         |                      "step"          The steps between each number
         |                      "float"         Boolean value to en/disable floating numbers.
         |
         |                  TYPE SPECIFIY "type => radio" and "type => checkbox"
         |                      "options"       A key => value array with all available checkboxes
         |                                      or radio fields. The "value" parameter may
         |                                      contain the key, which should be pre-selected.
         |
         |                  TYPE SPECIFIC "type => select"
         |                      "multiple"      TRUE to allow multiple selections, you need to pass
         |                                      an array on the "value" part to pre-select
         |                                      multiple options.
         |                      "options"       An key => value array with options, if the "value"
         |                                      part is an array too 'key' will handled as optgroup
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        public function configureOption($key, $config){
            if(!array_key_exists($key, $this->options) || !is_array($config)){
                return false;
            }

            // Validate Type
            $types = array(
                "text", "password", "number", "color", "checkbox", "radio",
                 "select", "image", "onoff", "pages"
            );
            if(isset($config["type"]) && !in_array($config["type"], $types)){
                unset($config["type"]);
            }
            unset($config["value"]);

            // Add Configuration
            $this->options[$key] = array_merge($this->options[$key], $config);
            return true;
        }

        /*
         |  API :: has AN OPTION
         |  @since  0.1.0
         |
         |  @param  string  The unique option KEY as STRING.
         |
         |  @return bool    TRUE if the option key exists, FALSE if not.
         */
        public function hasOption($key){
            return array_key_exists($key, $this->options);
        }

        /*
         |  API :: GET AN OPTION
         |  @since  0.1.0
         |
         |  @param  string  The unique option KEY as STRING.
         |  @param  multi   The default VALUE, which should return if the option KEY is missing.
         |
         |  @return multi   The respective option value on succes, $default otherwise.
         */
        public function getOption($key, $default = false){
            if(!array_key_exists($key, $this->options)){
                return $default;
            }
            if($this->options[$key]["value"] !== NULL){
                return $this->options[$key]["value"];
            }
            return $default;
        }

        /*
         |  API :: SET A MENU
         |  @since  0.1.0
         |
         |  @param  string  The unique menu position as STRING.
         */
        public function setMenu($position, $default = array()){
            if(array_key_exists($position, $this->menus) || !is_array($default)){
                return false;
            }
            $this->menus[$position] = array(
                "title"         => $position,
                "description"   => pd__("Unconfigured Menu!"),
                "value"         => $default
            );
            return true;
        }

        /*
         |  API :: CONFIGURE A MENU
         |  @since  0.1.0
         |
         |  @param  string  The unique menu position as STRING.
         |  @param  array   The additional menu interface configuration.
         |                      "title"         Set an custom menu title
         |                      "description"   Add a small menu description
         |                      "value"         The used menu item order
         |
         |  @return bool    TRUE if everything is fluffy, FALSE if not.
         */
        public function configureMenu($position, $config){
            if(!array_key_exists($position, $this->menus) || !is_array($config)){
                return false;
            }
            unset($config["value"]);
            $this->menus[$position] = array_merge($this->menus[$position], $config);
            return true;
        }

        /*
         |  API :: HAS A MENU
         |  @since  0.1.0
         |
         |  @param  string  The unique menu position as STRING.
         |
         |  @return bool    TRUE if the position exists and either value or default contains
         |                  an non-empty menu item ARRAY.
         */
        public function hasMenu($position){
            return array_key_exists($position, $this->menus);
        }

        /*
         |  API :: GET A MENU
         |  @since  0.1.0
         |
         |  @param  string  The unique menu position as STRING.
         |
         |  @return multi   The respective menu items array on success, FALSE otherwise.
         */
        public function getMenu($position){
            if(!array_key_exists($position, $this->menus)){
                return false;
            }
            if(!empty($this->menus[$position]["value"])){
                return $this->menus[$position]["value"];
            }
            return array();
        }
    }
