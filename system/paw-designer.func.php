<?php
/*
 |  paw.Designer - A advanced Theme Environment for Bludit
 |  @file       ./paw-designer.func.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    // Non-Plugin Storage
    global $paw_theme;
    $paw_theme = array(
        "menus"     => array(),
        "options"   => array()
    );


##
##  CORE FUNCTIONS
##

    /*
     |  THEME :: CHECKS FOR A VALID PAW.DESIGNER INSTANCE
     |  @since  0.1.0
     |
     |  @return multi   Returns the PawTheme instance on success, FALSE otherwise.
     */
    function pd_designer(){
        global $PawDesignerTheme;
        if(is_a($PawDesignerTheme, "PawTheme")){
            return $PawDesignerTheme->isTheme()? $PawDesignerTheme: false;
        }
        return false;
    }

    /*
     |  THEME :: LOAD THEME.PHP FILE
     |  @since  0.1.0
     */
    function pd_load_theme(){
        global $PawDesignerTheme, $site;
        if(is_a($PawDesignerTheme, "pawTheme")){
            return $PawDesignerTheme->loadConfig();
        }
        $theme = PATH_THEMES . $site->theme() . DS;

        // Load Meta
        $meta = file_get_contents($theme . "metadata.json");
        $meta = json_decode($meta, true);
        if(isset($meta["paw.designer-file"]) && file_exists($theme . $meta["paw.designer-file"])){
            $file = $meta["paw.designer-file"];
        } else {
            $file = "theme.json";
        }
        require_once($file);
        return true;
    }

    /*
     |  THEME :: RETURNS THE FULL URL
     |  @since  0.1.0
     |
     |  @...    string  A single or multiple path / file arguments.
     |
     |  @return string  The full URL including the passed path.
     */
    function pd_theme_url(){
        global $site;

        // Path
        $path = implode("/", array_map(function($i){ return trim($i, "/"); }, func_get_args()));
        $path = trim($path, "/");
        return DOMAIN_THEME . $path;
    }

    /*
     |  THEME :: RETURNS THE FULL PATH
     |  @since  0.1.0
     |
     |  @...    string  A single or multiple path / file arguments.
     |
     |  @return string  The full PATH including the passed path.
     */
    function pd_theme_path(){
        $path = implode(DS, array_map(function($i){ return trim($i, "/\\"); }, func_get_args()));
        $path = trim($path, DS);
        return THEME_DIR . str_replace(array("/", "\\"), DS, trim($path, "/\\"));
    }

    /*
     |  THEME :: INCLUDE PAGE PART
     |  @since  0.1.0
     |
     |  @...    string  A single or multiple path / file arguments.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function pd_get_part(){
        global $site;

        // Sanitize Arguments
        $path = implode("/", array_map(function($i){ return trim($i, "/"); }, func_get_args()));
        $path = trim($path, "/");
        if(strpos($path, ".php") !== strlen($path)-4){
            $path .= ".php";
        }

        // Load
        if(Sanitize::pathFile(PATH_THEMES . $site->theme() . DS, $path)){
            include(PATH_THEMES . $site->theme() . DS . $path);
            return true;
        }
        return false;
    }

    /*
     |  THEME :: WRITE SOME BODY CLASSES
     |  @since  0.1.0
     |
     |  @param  multi   A single custom class name as STRINg, multiple as ARRAY.
     |  @param  string  The prefix for the core classes as STRING.
     |
     |  @return string  A space-separated STRING full with body class names.
     */
    function pd_body_classes($custom = array(), $core = "page"){
        global $page, $site, $url;

        // Core Classes
        $classes = array("theme-" . $site->theme());
        if(pd_is_home(true)){
            $classes[] = "{$core}-home";
            $classes[] = empty($site->homepage())? "home-nonstatic": "home-static";
        } else if(pd_is_category()){
            $classes[] = "{$core}-category";
            $classes[] = "category-" . $url->slug();
        } else if(pd_is_tag()){
            $classes[] = "{$core}-tag";
            $classes[] = "tag-" . $url->slug();
        } else if(pd_is_404()){
            $classes[] = "{$core}-error";
            $classes[] = "error-404";
        } else if(pd_is_page()){
            $classes[] = "{$core}-single";
            $classes[] = "page-" . $page->slug();
            if(!empty($page->template())){
                $classes[] = "{$core}-template";
                $classes[] = "template-" . $page->template();
            }
        }

        // Add custom
        $custom = is_array($custom)? $custom: explode(" ", $custom);
        $classes = array_map(function($item){
            return trim(preg_replace("#[^a-z0-9_-]#", "-", strtolower($item)));
        }, array_merge($classes, $custom));
        return implode(" ", array_unique($classes));
    }

    /*
     |  THEME :: WRITE SOME BODY CLASSES
     |  @since  0.1.0
     |
     |  @param  object  The Page object, where the class names should be generated.
     |  @param  multi   A single custom class name as STRINg, multiple as ARRAY.
     |  @param  string  The prefix for the core classes as STRING.
     |
     |  @return string  A space-separated STRING full with body class names.
     */
    function pd_page_classes($page, $custom = array(), $core = "page"){
        if(!is_a($page, "Page")){
            return "";
        }

        // Cose Classes
        $classes = array("{$core}", "{$core}-" . $page->type());
        if($page->allowComments()){
            $classes[] = "comments-enabled";
        } else {
            $classes[] = "comments-disabled";
        }
        $classes[] = "category-" . $page->category();
        $classes = array_merge($classes, array_map(function($item){
            return "tag-{$item}";
        }, $page->tags(true)));

        // Add custom
        $custom = is_array($custom)? $custom: explode(" ", $custom);
        $classes = array_map(function($item){
            return trim(preg_replace("#[^a-z0-9_-]#", "-", strtolower($item)));
        }, array_merge($classes, $custom));
        return implode(" ", array_unique($classes));
    }


##
##  CUSTOMIZER FUNCTIONS
##

    /*
     |  THEME :: SET OPTION
     |  @since  0.1.0
     |
     |  @param  string  The unique option KEY as STRING.
     |  @param  multi   The default option VALUE, which gets overwritten if the paw.Designer
     |                  Plugin is loaded and configured by the user.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function pd_set_option($key, $default = NULL){
        global $paw_theme;

        if(($theme = pd_designer()) !== false){
            return $theme->setOption($key, $default);
        }
        $paw_theme["options"][$key] = $default;
        return true;
    }

    /*
     |  THEME :: HAS OPTION
     |  @since  0.1.0
     |
     |  @param  string  The unique option KEY as STRING.
     |
     |  @return bool    TRUE if the option key exists, FALSE if not.
     */
    function pd_has_option($key){
        global $paw_theme;

        if(($theme = pd_designer()) !== false){
            return $theme->hasOption($key);
        }
        return array_key_exists($key, $paw_theme["options"]);
    }

    /*
     |  THEME :: GET OPTION
     |  @since  0.1.0
     |
     |  @param  string  The unqiue option KEY as STRING.
     |  @param  multi   The default VALUE, which should return if the option KEY is missing.
     |
     |  @return multi   The respective option value on succes, $default otherwise.
     */
    function pd_get_option($key, $default = false){
        global $paw_theme;

        if(($theme = pd_designer()) !== false){
            return $theme->getOption($key, $default);
        }
        if(array_key_exists($key, $paw_theme["options"]) && $paw_theme["options"][$key] !== NULL){
            return $paw_theme["options"][$key];
        }
        return $default;
    }

    /*
     |  THEME :: SET MENU ARRAY
     |  @since  0.1.0
     |
     |  @param  string  The unique menu position as STRING.
     |  @param  array   The default menu item ARRAY, which gets overwritten if the paw.Designer
     |                  Plugin is laoded and configured by the user.
     |
     |  @return bool    TRUE if everything is fluffy, FALSE if not.
     */
    function pd_set_menu($pos, $default = array()){
        global $paw_theme;

        if(($theme = pd_designer()) !== false){
            return $theme->setMenu($pos, $default);
        }
        $paw_theme["menus"][$pos] = $default;
        return true;
    }

    /*
     |  THEME :: HAS MENU ARRAY
     |  @since  0.1.0
     |
     |  @param  string  The unique menu position as STRING.
     |
     |  @return bool    TRUE if the position exists and either value or default contains
     |                  an non-empty menu item ARRAY.
     */
    function pd_has_menu($pos){
        global $paw_theme;

        if(($theme = pd_designer()) !== false){
            return $theme->hasMenu($pos);
        }
        if(!isset($paw_theme["menus"][$pos])){
            return false;
        }
        return !(empty($paw_theme["menus"][$pos]));
    }

    /*
     |  THEME :: GET MENU ARRAY
     |  @since  0.1.0
     |
     |  @param  string  The unique menu position as STRING.
     |
     |  @return multi   The respective menu items array on success, FALSE otherwise.
     */
    function pd_get_menu($pos){
        global $paw_theme;

        if(($theme = pd_designer()) !== false){
            return $theme->getMenu($pos);
        }
        return isset($paw_theme["menus"][$pos])? $paw_theme["menus"][$pos]: false;
    }

    /*
     |  THEME :: RENDER MENU ARRAY
     |  @since  0.1.0
     |
     |  @param  string  The unique menu position as STRING.
     |  @param  array   Some additional render configurations.
     |
     |  @return multi   The rendered HTML content id the "print" config is false, NULL otherwise.
     */
    function pd_render_menu($pos, $config = array()){
        if(($menu = pd_get_menu($pos)) !== false){
            $render = new PDRenderMenu($config);
            return $render->render($menu);
        }
        return "";
    }


##
##  SYSTEM FUNCTIONS
##

    /*
     |  QUERY PAGES
     |  @since  0.1.0
     |
     |  @param  array   The respective Pages Query with zero or more settings:
     |                      "type"          A single type as STRING, multiple as ARRAY:
     |                                      DEFAULT: ("published", "sticky")
     |                      "author"        A single author name as STRING, multiple as ARRAY.
     |                      "cover"         TRUE if it MUST contain a cover image, FALSE if it
     |                                      MUST NOT have an image. (None / Null for both)
     |                      "search"        The search string.
     |                      "search_in"     The respective fields, where you want to search for.
     |                                      DEFAULT: ("title", "description")
     |                      "tags"          A single tag as STRING, multiple as ARRAY. This option
     |                                      is used as "AND / OR", so the pages MUST contain at
     |                                      least a single tag of your list, but not all.
     |                      "tags_in"       A single tag as STRING, multiple as ARRAY. This option
     |                                      is used as "AND", so the pages MUST contain ALL tags.
     |                      "tags_not"      A single tag as STRING, multiple as ARRAY. This option
     |                                      is used as "AND", to the pages MUST contain NONE of
     |                                      the tags within your list.
     |                      "category"      A single category as STRING, multiple as ARRAY.
     |                      "comments"      TRUE if the pages MUST allow comments, FALSE if the
     |                                      MUST be disallowed. (None / Null for both).
     |                      "template"      A single template as STRING, multiple as ARRAY.
     |                      "parent"        A single parent slug as STRING, multiple as ARRAY.
     |                                      Use "/" to just include top-level pages.
     |                      "exclude"       Exclude pages using the slug name(s).
     |
     |
     |                      "sticky"        TRUE to stick sticky posts to the top of the list
     |                                      FALSE to skip sticky posts, NULL to handle this
     |                                      pages as all other pages too. (Default: TRUE)
     |                      "limit"         The number of paged, which should return
     |                      "paged"         The page offset.
     |
     |  @erturn array   The respective pages array (or an empty array) on success,
     |                  FALSE on failure.
     */
    function pd_query_pages($query = array()){
        global $url, $site, $pages;
        global $paw_temp_next, $paw_temp_prev;
        if(!is_array($query) || !is_array($pages->getDB())){
            return false;
        }

        // Get Query
        $query = array_merge(array(
            "type"      => array("published", "sticky"),
            "sticky"    => $url->pageNumber() == 1,
            "limit"     => $site->itemsPerPage(),
            "paged"     => $url->pageNumber()
        ), $query);
        $paw_temp_next = false;
        $paw_temp_prev = $url->pageNumber() > 1;

        // Prepare Arrays
        $arr = array("type", "author", "tags", "tags_in", "tags_not", "category", "template", "parent", "exclude");
        foreach($arr AS $key){
            if(!isset($query[$key])){
                unset($query[$key]);
                continue;
            }
            if(!is_array($query[$key])){
                $query[$key] = array($query[$key]);
            }
        }

        // Filter Pages
        $skip = 0;
        $count = 0;
        $return = array();
        $sticky = array();

        // Loop
        $loop = array("type", "author", "template", "category");
        foreach($pages->getDB() AS $slug => $page){
            foreach($loop AS $key){
                if(isset($query[$key]) && !in_array($page[$key], $query[$key], true)){
                    $slug = false;
                    break;
                }
            }
            if(!$slug){
                continue;
            }

            // Exclude
            if(isset($query["exclude"]) && in_array($slug, $query["exclude"])){
                continue;
            }

            // Cover Image
            if(array_key_exists("cover", $query)){
                if($query["cover"] === true && empty($page["coverImage"])){
                    continue;
                } else if($query["cover"] === false && !empty($page["coverImage"])){
                    continue;
                }
            }

            // Comments
            if(array_key_exists("comments", $query)){
                if($query["comments"] === true && empty($page["allowComments"])){
                    continue;
                } else if($query["comments"] === false && !empty($page["allowComments"])){
                    continue;
                }
            }

            // Tags
            if(isset($query["tags"])){
                if(empty($page["tags"])){
                    continue;
                }
                if(count($query["tags"]) === array_diff($query["tags"], $page["tags"])){
                    continue;
                }
            }

            // Tags In
            if(isset($query["tags_in"])){
                if(empty($page["tags"])){
                    continue;
                }
                if(count($query["tags_in"]) - array_diff($query["tags_in"], $page["tags"]) !== 0){
                    continue;
                }
            }

            // Tags Not
            if(isset($query["tags_not"])){
                if(count($query["tags_not"]) !== array_diff($query["tags_not"], $page["tags"])){
                    continue;
                }
            }

            // Parents
            if(isset($query["parent"]) && !empty($query["parent"])){
                if($query["parent"][0] === "/" && strpos($slug, "/") !== false){
                    continue;
                }
                $parent = explode("/", $slug);
                if(!in_array($parent[0], $query["parent"], true)){
                    continue;
                }
                if(count($parent) === 1){
                    continue;
                }
            }

            // Search
            if(isset($query["search"])){
                if(strpos($page["title"], $query["search"]) === false){
                    continue;
                }
                if(strpos($page["description"], $query["search"]) === false){
                    continue;
                }
            }

            // Skip
            if(isset($query["paged"]) && $query["paged"] > 1){
                if($skip < (($query["paged"]-1) * $query["limit"])){
                    $skip++;
                    continue;
                }
            }

            // Counter
            if($count == $query["limit"]){
                $paw_temp_next = true;
                break;
            }

            // Sticky
            if($page["type"] === "sticky" && $query["sticky"] === false){
                continue;
            }
            if($page["type"] === "sticky" && $query["sticky"] === true){
                $sticky[] = new Page($slug);
                continue;
            }

            // Add
            $count++;
            $return[] = new Page($slug);
        }
        return array_merge($sticky, $return);
    }

    /*
     |  IS NEXT PAGE
     |  @since  0.1.0
     |
     |  @return bool    TRUE if there is a next page, FALSE if not.
     */
    function pd_is_next_page(){
        global $paw_temp_next;
        if(isset($paw_temp_next)){
            return $paw_temp_next;
        }
        return false;
    }

    /*
     |  GET NEXT PAGE URL
     |  @since  0.1.0
     |
     |  @param  bool    TRUE to force "a next page" even if there is none.
     |
     |  @return string  The URL for the next posts page, or an empty string.
     */
    function pd_next_page($force = false){
        global $url;
        if(pd_is_next_page() || $force === true){
            return $url->uri() . "?page=" .  ($url->pageNumber()+1);
        }
        return "";
    }

    /*
     |  IS PREVIOUS PAGE
     |  @since  0.1.0
     |
     |  @return bool    TRUE if there is a previous page, FALSE if not.
     */
    function pd_is_prev_page(){
        global $url, $paw_temp_prev;
        if(isset($paw_temp_prev)){
            return $paw_temp_prev;
        }
        return $url->pageNumber > 1;
    }

    /*
     |  GET PREVIOUS PAGE URL
     |  @since  0.1.0
     |
     |  @param  bool    TRUE to force "a previous page" even if there is none.
     |
     |  @return string  The URL for the previous posts page, or an empty string.
     */
    function pd_prev_page($force){
        global $url;
        if(pd_is_preg_page() || $force === true){
            return $url->uri() . "?page=" .  ($url->pageNumber()-1);
        }
        return "";
    }

    /*
     |  WRITE A PAGE EXCERPT
     |  @since  0.1.0
     |
     |  @param  object  The respective Page Object.
     |  @param  int     The desired length of the excerpt.
     |  @param  string  The ending, fi the content is too long.
     |  @param  bool    TRUE to force the length and break words, FALSE to hanle it softer.
     |
     |  @return string  The respective excerpt of the article on success, FALSE otherwise.
     */
    function pd_page_excerpt($page, $length = 300, $ending = "...", $force = false){
        if(!is_a($page, "Page")){
            return false;
        }

        // Check
        $content = strip_tags($page->content());
        if(strlen($content) <= $length+5){
            return $content;
        }

        // Force Length
        if($force){
            $content = substr($content, 0, $length-strlen(strip_tags($ending)));
            return $content . $ending;
        }

        // Unforced
        $content = wordwrap($content, $length, "\n", true);
        $content = explode("\n", $content)[0];
        return $content . $ending;
    }


##
##  SHORTCODE FUNCTIONS
##

    /*
     |  THENE :: TRANSLATE STRING
     |  @since  0.1.0
     |
     |  @param  string  The translations tring, which you want to translate.
     |
     |  @return string  The respective translation on success, the passed data on failure.
     */
    function pd__($string){
        global $L;
        return $L->get($string);
    }
    function pd_e($string){
        global $L;
        print($L->get($string));
    }

    /*
     |  CHECK :: CURRENT VIEW == HOMEPAGE
     |  @since  0.1.0
     |
     |  @param  bool    TRUE to check for statis homepages too, FALSE to do it not.
     |
     |  @return bool    TRUE on the (static) homepage view, FALSE otherwise.
     */
    function pd_is_home($static = true){
        global $url, $site;
        if($url->whereAmI() === "home"){
            return true;
        }
        return ($static)? $url->slug() === $site->homepage(): false;
    }

    /*
     |  CHECK :: CURRENT VIEW == ERROR 404
     |  @since  0.1.0
     |
     |  @return bool    TRUE on the Error 404 view, FALSE otherwise.
     */
    function pd_is_404(){
        global $url;
        return ($url->httpCode() === 404);
    }

    /*
     |  CHECK :: CURRENT VIEW == SEARCH
     |  @since  0.1.0
     |
     |  @return bool    TRUE on the Search view, FALSE otherwise.
     */
    function pd_is_search(){
        global $url;
        return ($url->whereAmI() === "search");
    }

    /*
     |  CHECK :: CURRENT VIEW == CATEGORY
     |  @since  0.1.0
     |
     |  @param  string  The desired category slug as STRING, which you want to check for.
     |                  Keep it to NULL to check for category views in General.
     |
     |  @return bool    TRUE if (specific) category view, FALSE if not.
     */
    function pd_is_category($category = NULL){
        global $url;
        if($url->whereAmI() !== "category"){
            return false;
        }
        return ($category == NULL)? true: $url->slug() == $category;
    }

    /*
     |  CHECK :: CURRENT VIEW == PAGED
     |  @since  0.1.0
     |
     |  @return bool    TRUE if you're on a paged page, FALSE if not.
     */
    function pd_is_paged(){
        global $url;
        return $url->pageNumber() > 1;
    }

    /*
     |  CHECK :: CURRENT VIEW == TAG
     |  @since  0.1.0
     |
     |  @param  string  The desired tag slug as STRING, which you want to check for.
     |                  Keep it to NULL to check for tag views in General.
     |
     |  @return bool    TRUE if (specific) tag view, FALSE if not.
     */
    function pd_is_tag($tag = NULL){
        global $url;
        if($url->whereAmI() !== "tag"){
            return false;
        }
        return ($tag == NULL)? true: $url->slug() == $tag;
    }

    /*
     |  CHECK :: CURRENT VIEW == PAGE
     |  @since  0.1.0
     |
     |  @return bool    TRUE on single Page views (except 404), FALSE otherwise.
     */
    function pd_is_page(){
        global $url;
        return ($url->whereAmI() === "page" && $url->httpCode() !== 404);
    }

    /*
     |  CHECK :: CURRENT VIEW == STATIC PAGE
     |  @since  0.1.0
     |
     |  @return bool    TRUE on single Page views (except 404), FALSE otherwise.
     */
    function pd_is_static(){
        global $url, $page;
        return ($url->whereAmI() === "page" && $page->type() === "static");
    }

    /*
     |  CHECK :: CURRENT VIEW == PAGE LAYOUT
     |  @since  0.1.0
     |
     |  @param  string  The desired template as STRING, which you want to check for.
     |                  Keep it to NULL to check if the page uses and template in General.
     |
     |  @return bool    TRUE if the page requests a (specific) template, FALSE if not.
     */
    function pd_is_template($template = NULL){
        global $url, $page;
        if($url->whereAmI() !== "page" || $url->httpCode() === 404){
            return false;
        }
        return ($template == NULL)? !empty($page->template()): $page->template() == $template;
    }


##
##  HELPER CLASSES
##

    /*
     |  RENDER MENU CLASS
     |  @since  0.1.0
     */
    class PDRenderMenu{
        /*
         |  CONFIGURATION
         */
        private $default = array(
            "container"     => "div.menu-container",
            "menu"          => "ul.menu.menu-{level}",
            "item"          => "li.menu-item.menu-item-{slug}#menu-item-{slug}",
            "link"          => "a.menu-link",
            "active"        => "menu-item-active",              // The current item
            "parent"        => "menu-item-active-parent",       // The "slug-based-parent" of the current item
            "anchor"        => "menu-item-active-anchor",       // The "array-based-parent" of the current item
            "children"      => "menu-item-has-children",
            "before"        => "",                              // Within <a> BEFORE the title
            "after"         => "",                              // Within <a> AFTER  the title
            "depth"         => 9,                               // Maximum children depth
            "print"         => false
        );

        /*
         |  INSTANCE VARs
         */
        protected $menu = array();
        protected $depth = 0;
        protected $config = array();
        protected $content = "";

        /*
         |  TEMP VARs
         */
        protected $active = array();

        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct($config = array()){
            $this->config = $this->default;
            if(is_array($config)){
                $this->config = array_merge($this->config, $config);
            }

            // Parse Selector
            $this->config["container"] = $this->parse($this->config["container"]);
            $this->config["menu"] = $this->parse($this->config["menu"]);
            $this->config["item"] = $this->parse($this->config["item"]);
            $this->config["link"] = $this->parse($this->config["link"]);
        }

        /*
         |  INTERNAL :: PARSE SELECTOR
         |  @since  0.1.0
         */
        private function parse($string, $type = "elem"){
            $string = !is_string($string)? "": $string;
            $return = array("elem" => "", "id" => "", "class" => "");
            foreach(preg_split("#([\#\.])#", $string, -1, PREG_SPLIT_DELIM_CAPTURE) AS $item){
                if($item == "." || $item == "#"){
                    $type = ($item == ".")? "class": "id";
                    continue;
                }
                $return[$type] .= (!empty($return[$type])? " ": "") . $item;
            }
            return $return;
        }

        /*
         |  INTERNAL :: ACTIVE
         |  @since  0.1.0
         */
        private function active($slug, $item){
            global $url, $site;

            if($url->slug() == $site->homepage() && trim($slug, "/") == ""){
                return $this->config["active"];
            }
            if(($a = trim($url->slug(), "/")) == ($b = trim($slug, "/"))){
                return $this->config["active"];
            }
            if(!empty($a) && !empty($b) && strpos($a, $b) === 0){
                return $this->config["parent"];
            }

            // Find Anchor
            if(isset($item["children"])){
                foreach($item["children"] AS $key => $value){
                    if($this->active($key, $value) !== false){
                        return $this->config["anchor"];
                    }
                }
            }
            return false;
        }

        /*
         |  INTERNAL :: RENDER ATTRIBUTES
         |  @since  0.1.0
         */
        private function attributes($attr){
            if(!is_array($attr)){
                return "";
            }
            $return = "";
            foreach($attr AS $key => $value){
                if(empty($value)){
                    continue;
                }
                $return .= " {$key}=\"{$value}\"";
            }
            return $return;
        }

        /*
         |  RENDER MENU
         |  @since  0.1.0
         */
        public function render($menu, $print = false){
            if(!is_array($menu)){
                return false;
            }

            // Loop
            $this->depth++;
            if($this->depth == 1){
                $this->create("container");
            }
            $this->create("menu");
            foreach($menu AS $slug => $item){
                $this->create("item", $slug, $item);
                $this->create("link", $slug, $item);
                $this->content .= $this->config["before"] . $item["title"] . $this->config["after"];
                $this->create("/link");
                if(isset($item["children"]) && $this->depth < $this->config["depth"]){
                    $this->render($item["children"], false);
                }
                $this->create("/item");
            }
            $this->create("/menu");
            if($this->depth == 1){
                $this->create("/container");
            }
            $this->depth--;

            // Return
            if($this->depth == 0){
                if(!$print){
                    return $this->content;
                }
                print($this->content);
            }
        }

        /*
         |  CREATE ELEMENT
         |  @since  0.1.0
         */
        public function create($type, $slug = "", $item = ""){
            global $site;

            // Check Call
            if(!isset($this->config[ltrim($type, "/")]) || empty($this->config[ltrim($type, "/")]["elem"])){
                return false;
            }
            $conf = $this->config[ltrim($type, "/")];

            // Render Closing Element
            if($type[0] == "/"){
                $this->content .= "</{$conf["elem"]}>";
                return true;
            }

            // Handle Attributes
            $replace = array(
                "{level}" => $this->depth,
                "{sub}"   => str_repeat("sub", $this->depth-1),
                "{-sub}"  => str_repeat("-sub", $this->depth-1),
                "{sub-}"  => str_repeat("sub-", $this->depth-1),
                "{-sub-}" => str_repeat("-sub-", $this->depth-1),
                "{slug}"  => trim(preg_replace("#[^a-zA-Z0-9_-]#", "-", $slug), "-")
            );
            $attr = array_map(function($string) use ($replace){
                return str_replace(array_keys($replace), array_values($replace), $string);
            }, $conf);
            unset($attr["elem"]);

            // Handle Items
            if($type == "item"){
                if(($active = $this->active($slug, $item)) !== false){
                    $attr["class"] .= " {$active}";
                }
                if((isset($item["children"]) && $this->depth < $this->config["depth"])){
                    $attr["class"] .= " {$this->config["children"]}";
                }
            }

            // Handle Links
            if($type == "link"){
                $attr["href"] = $site->url() . trim($slug, "/");
            }

            // Render Opening Element
            $this->content .= "<{$conf["elem"]}".$this->attributes($attr).">";
            return true;
        }
    }
