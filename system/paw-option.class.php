<?php
/*
 |  paw.Designer - A advanced Theme Engine for Bludit
 |  @file       ./system/paw-theme.func.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class PawOption{
        /*
         |  GLOBAL ATTRIBUTES
         */
        static private $globals = array("id", "name", "class");

        /*
         |  HELPER :: BUILD FIELD
         |  @since  0.1.0
         */
        static private function build($tag = "input", $attrs = array(), $validate = array()){
            if(!isset($attrs["class"])){
                $attrs["class"] = "";
            }
            if(in_array($tag, array("input", "textarea", "select"))){
                if(isset($attrs["type"]) && in_array($attrs["type"], array("radio", "checkbox"))){
                    $attrs["class"] .= "custom-control-input";
                } else {
                    $attrs["class"] .= " form-control" . ($tag == "select"? " custom-select": "");
                }
            }

            // Prepare Attributes
            $attributes = array();
            foreach($attrs AS $key => $value){
                if(!in_array($key, self::$globals) && !in_array($key, $validate) && strpos($key, "data-") === false){
                    continue;
                }
                if($value === false || $value === null){
                    $value = "0";
                } else if ($value === true){
                    $value = "1";
                } else if(!is_string($value)){
                    $value = (string) $value;
                } else {
                    $value = Sanitize::html($value);
                }
                $attributes[] = "{$key}=\"{$value}\"";
            }
            $attributes = implode(" ", $attributes);

            // Render
            if($tag === "input"){
                return "<{$tag} {$attributes} />";
            }
            $inner = isset($attrs["inner"])? $attrs["inner"]: "";
            return "<{$tag} {$attributes}>{$inner}</{$tag}>";
        }

        /*
         |  RENDER TEXT FIELD
         |  @since  0.1.0
         */
        static public function text($id, $config){
            if(!is_array($config)){
                $config = array();
            }
            $config = array_merge($config, array(
                "id"        => $id,
                "name"      => "query[config][{$id}]",
                "type"      => "text"
            ));
            return self::build("input", $config, array("type", "value", "placeholder"));
        }

        /*
         |  RENDER PASSWORD FIELD
         |  @since  0.1.0
         */
        static public function password($id, $config){
            if(!is_array($config)){
                $config = array();
            }
            $config = array_merge($config, array(
                "id"        => $id,
                "name"      => "query[config][{$id}]",
                "type"      => "password"
            ));
            return self::build("input", $config, array("type", "value", "placeholder"));
        }

        /*
         |  RENDER NUMBER FIELD
         |  @since  0.1.0
         */
        static public function number($id, $config){
            if(!is_array($config)){
                $config = array();
            }
            $config = array_merge($config, array(
                "id"        => $id,
                "name"      => "query[config][{$id}]",
                "type"      => "number"
            ));
            return self::build("input", $config, array("type", "value", "min", "max", "step", "placeholder"));
        }

        /*
         |  RENDER RADIO FIELD
         |  @since  0.1.0
         */
        static public function radio($id, $config){
            if(!is_array($config) || !isset($config["options"]) || empty($config["options"])){
                return "";
            }

            // Render Options
            $options = array();
            foreach($config["options"] AS $key => $value){
                $attr = array_merge($config, array(
                    "id"        => "{$id}-{$key}",
                    "name"      => "query[config][{$id}]",
                    "value"     => "{$key}",
                    "type"      => "radio"
                ));
                if(pd_checked($key, $attr["value"], 0)){
                    $attr["checked"] = "checked";
                }

                // Single Radiofield
                $before = "<div class=\"custom-control custom-radio\">";
                $field = self::build("input", $attr, array("type", "value", "checked", "placeholder"));
                $label = "<label for=\"{$id}-{$key}\" class=\"custom-control-label\">{$value}</label>";
                $options[] = $before . $field . $label . "</div>";
            }
            return implode("\n", $options);
        }

        /*
         |  RENDER CHECKBOX FIELD
         |  @since  0.1.0
         */
        static public function checkbox($id, $config){
            if(!is_array($config) || !isset($config["options"]) || empty($config["options"])){
                return "";
            }

            // Render Options
            $options = array();
            foreach($config["options"] AS $key => $value){
                $attr = array_merge($config, array(
                    "id"        => "{$id}-{$key}",
                    "name"      => "query[config][{$id}][]",
                    "value"     => "{$key}",
                    "type"      => "checkbox"
                ));
                if(pd_checked($key, $attr["value"], 0)){
                    $attr["checked"] = "checked";
                }

                // Single Checkboxfield
                $before = "<div class=\"custom-control custom-checkbox\">";
                $field = self::build("input", $attr, array("type", "value", "checked", "placeholder"));
                $label = "<label for=\"{$id}-{$key}\" class=\"custom-control-label\">{$value}</label>";
                $options[] = $before . $field . $label . "</div>";
            }
            return implode("\n", $options);
        }

        /*
         |  RENDER SELECT FIELD
         |  @since  0.1.0
         */
        static public function select($id, $config){
            if(!is_array($config) || !isset($config["options"]) || empty($config["options"])){
                return "";
            }
            $options = array();

            // Render Options
            $loop = $config["options"];
            $return = &$options;
            while(is_array($config["options"]) && count($config["options"]) > 0){
                $value = key($loop);
                $label = current($loop);

                // Sub Loop through Optgroup
                if(!is_string($label)){
                    if(!isset($group) && is_array($label) && count($label) > 0){
                        $key = $value;
                        $loop = $label;
                        $group = array();
                        $return = &$group;
                    }
                    continue;
                }

                // Render Optionfield
                $attr = array("value" => $value, "inner" => $label);
                if(pd_selected($value, $config["value"], 0)){
                    $attr["selected"] = "selected";
                }
                $return[] = self::build("option", $attr, array("value", "selected"));

                // Next
                if(!next($loop)){
                    if(isset($group)){
                        $attr = array("label" => $key, "inner" => "\n" . implode("\n", $return));
                        $options[] = self::build("optgroup", $attr, array("label"));
                        unset($group);
                        unset($key);

                        // Return to Main Loop
                        $loop = $config["options"];
                        $return[] = &$options;
                        if(!next($loop)){
                            break;
                        }
                    }
                    break;
                }
            }

            // Render Select
            $attr = array(
                "id"    => $id,
                "name"  => "query[config][{$id}]" . ((isset($config["multiple"]) && $config["multiple"])? "[]": ""),
                "inner" => "\n" . implode("\n", $options)
            );
            return self::build("select", $attr, array("multiple"));
        }

        /*
         |  RENDER IMAGE FIELD
         |  @since  0.1.0
         */
        static public function image($id, $config){
            if(!isset($config["placeholder"])){
                $config["placeholder"] = pd__("Enter a image URL or press the button to upload a new one.");
            }
            $attr = array(
                "id"            => $id,
                "name"          => "query[config][{$id}]",
                "type"          => "text",
                "value"         => $config["value"],
                "data-media"    => "value",
                "placeholder"   => $config["placeholder"],
            );

            // Render
            ob_start();
            ?>
                <div class="paw-form-image">
                    <?php echo self::build("input", $attr, array("type", "value", "placeholder")); ?>
                    <button id="upload-<?php echo $id; ?>" data-handle="media"><?php pd_e("Upload Image"); ?></button>
                    <div class="paw-form-image-preview" data-media="preview"><?php
                        if(!empty($attr["value"])){
                            ?><img src="<?php echo $attr["value"]; ?>" /><?php
                        }
                    ?></div>
                </div>
            <?php
            $return = ob_get_contents();
            ob_end_clean();
            return $return;
        }

        /*
         |  RENDER PAGES FIELD
         |  @since  0.1.0
         */
        static public function pages($id, $config){
            if(!isset($config["placeholder"])){
                $config["placeholder"] = pd__("Start typing a page title to see a list of suggestions.");
            }

            // Hidden Field
            $attr1 = array(
                "id"        => $id,
                "name"      => "query[config][{$id}]",
                "value"     => $config["value"],
                "type"      => "hidden",
            );

            // Shown Field
            $attr2 = array(
                "type"          => "text",
                "placeholder"   => $config["placeholder"],
                "data-handle"   => "pages",
                "data-target"   => "#{$id}",
            );
            if(!empty($config["value"])){
                $page = new Page($config["value"]);
                $attr2["value"] = $page->title();
            }

            // Return
            $pass = array("type", "value", "placeholder");
            return self::build("input", $attr1, $pass)."\n".self::build("input", $attr2, $pass);
        }

        /*
         |  RENDER COLOR FIELD
         |  @since  0.1.0
         */
        static public function color($id, $config){
            $config = array_merge($config, array(
                "id"            => $id,
                "name"          => "query[config][{$id}]",
                "type"          => "text"
            ));

            ob_start();
            ?>
                <div class="input-group" data-handle="color">
                    <?php echo self::build("input", $config, array("type", "value", "placeholder")); ?>
                    <span class="input-group-append">
                        <span class="input-group-text colorpicker-input-addon"><i></i></span>
                    </span>
                </div>
            <?php
            $return = ob_get_contents();
            ob_end_clean();
            return $return;
        }

        /*
         |  RENDER SWITCH FIELD
         |  @since  0.1.0
         */
        static public function onoff($id, $config){
            $attr = array_merge($config, array(
                "id"        => "{$id}",
                "name"      => "query[config][{$id}]",
                "value"     => "true",
                "type"      => "checkbox",
                "class"     => "switch"
            ));
            if($config["value"] == "true" || $config["value"] == true || $config["value"] == "1"){
                $attr["checked"] = "checked";
            }

            // Single Checkboxfield
            $before = "<div class=\"switch\">";
            $field = self::build("input", $attr, array("type", "value", "checked", "placeholder"));
            $label = "<label for=\"{$id}\" class=\"custom-control-switch\"></label>";
            return $before . $field . $label . "</div>";
        }
    }
