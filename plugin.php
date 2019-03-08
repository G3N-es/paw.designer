<?php
/*
 |  paw.Designer - A advanced Theme Engine for Bludit
 |  @file       ./plugin.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }

    class PawDesigner extends Plugin{
        /*
         |  GLOBAL :: IS BACKEND OR FRONTEND?
         */
        private $backend = false;

        /*
         |  GLOBAL :: IS CURENT REQUEST AJAX BASED?
         */
        private $isAJAX = false;

        /*
         |  GLOBAL :: THE CURRENT ADMIN PLUGIN PAGE
         */
        private $adminPage = NULL;

        /*
         |  GLOBAL :: THE CURRENT ADMIN QUERY
         */
        private $adminQuery = array();


        /*
         |  CONSTRUCTOR
         |  @since  0.1.0
         */
        public function __construct(){
            global $PawDesignerPlugin;
            $PawDesignerPlugin = $this;

            // Apply Plugin Constructor
            parent::__construct();
        }

        /*
         |  HANDLER :: FORM RESPONSE
         |  @since  0.1.0
         */
        private function handleResponse($status, $data = array()){
            global $url;

            // POST Redirect
            if(!$this->isAJAX){
                if($status){
                    Alert::set($data["success"]);
                } else {
                    Alert::set($data["alert"], ALERT_STATUS_FAIL);
                }
                $action = isset($_GET["action"])? $_GET["action"]: $_POST["action"];
                Redirect::url(HTML_PATH_ADMIN_ROOT . $url->slug() . "#{$action}");
                die();
            }

            // AJAX Print
            if(!is_array($data)){
                $data = array();
            }
            $data["status"] = ($status)? "success": "error";
            $data = json_encode($data);

            header("Content-Type: application/json");
            header("Content-Length: " . strlen($data));
            print($data);
            die();
        }

        /*
         |  HANDLER :: VALIDATE REQUEST
         |  @since  0.1.0
         */
        private function handleRequest(){
            global $url, $login;

            // Check for valid Request
            $slug = $url->explodeSlug();
            if(count($slug) < 2 || !$login->isLogged()){
                return false;
            }
            if($slug[0] != "designer" && ($slug[0] != "ajax" || $slug[1] != "paw-designer")){
                return false;
            }
            if(!isset($_GET["action"]) && !isset($_POST["action"])){
                return false;
            }
            $this->isAJAX = ($slug[0] != "designer");

            // Basic Validation
            $data = isset($_GET["action"])? $_GET: $_POST;
            if(!isset($data["action"]) || !isset($data["query"]) || !isset($data["nonce"])){
                return false; // Don't return an error on incomplete calls
            }
            return $data;
        }

        /*
         |  HANDLER :: DATABASE AND THEME
         |  @since  0.1.0
         */
        private function handleTheme($query){
            if(!isset($query["theme-id"])){
                return false;
            }
            if(!isset($query["config"]) && !isset($query["menu-id"])){
                return false;
            }
            include_once("system/paw-designer.func.php");
            include_once("system/paw-theme.class.php");
            include_once("system/paw-theme.func.php");

            // Init DataBase
            $temp = new dbJSON($this->filenameDb);
            $this->db = $temp->db;
            if(!array_key_exists($query["theme-id"], $this->db)){
                $this->db[$query["theme-id"]] = array(
                    "menus"     => array(),
                    "options"   => array()
                );
            }

            // Init Theme
            $theme = new PawTheme($query["theme-id"], true);
            if(!$theme->isTheme() || (isset($query["menu-id"]) && !$theme->hasMenu($query["menu-id"]))){
                return false;
            }
            $this->db[$query["theme-id"]] = $theme->getOptions(true);
            return $theme;
        }

        /*
         |  HANDLER :: HANDLE REQUESTS
         |  @since  0.1.0
         */
        private function handle(){
            global $L, $security;

            // Check for AJAX Call
            if(($data = $this->handleRequest()) === false){
                return false;
            }
            if(!$security->validateTokenCSRF($data["nonce"])){
                return $this->handleResponse(false, array(
                    "error" => $L->g("pd-pluin-001")
                ));
            }
            $query = is_array($data["query"])? $data["query"]: array();

            // Handle BUILD Action
            if($data["action"] === "build"){
                if(!isset($query["slug"]) || !isset($query["title"])){
                    return $this->handleResponse(false, array(
                        "error" => $L->g("pd-plugin-002"),
                    ));
                }
                if(($content = $this->buildMenuItem($query["slug"], $query)) === false){
                    return $this->handleResponse(false, array(
                        "error" => $L->g("pd-plugin-003"),
                    ));
                }
                return $this->handleResponse(true, array(
                    "success" => $L->g("pd-plugin-004"),
                    "query" => $query,
                    "content" => $content
                ));
            }


            // Prepare Other Requests
            if(($theme = $this->handleTheme($query)) === false){
                return $this->handleResponse(false, array(
                    "error" => $L->g("pd-plugin-005")
                ));
            }

            // Handle Theme Config
            if($data["action"] === "options"){
                foreach((isset($query["config"])? $query["config"]: array()) AS $key => $value){
                    if(!$theme->hasOption($key)){
                        continue;
                    }
                    $this->db[$theme->getTheme()]["options"][$key] = $value;
                }
                $this->save();

                return $this->handleResponse(true, array(
                    "success" => $L->g("pd-plugin-006"),
                ));
            }

            // Handle Theme Menu
            if($data["action"] === "menu"){
                $menu = array();
                $this->db[$theme->getTheme()]["menus"][$query["menu-id"]] = $query["menu"];
                $this->save();

                return $this->handleResponse(true, array(
                    "success" => $L->g("pd-plugin-007"),
                    "data" => $query["menu"]
                ));
            }

            // Invalid
            return $this->handleResponse(false, array(
                "error" => $L->g("pd-plugin-005")
            ));
        }

        /*
         |  PLUGIN :: INIT DATABASE
         |  @since  0.1.0
         */
        public function init(){
            global $url;
            $this->backend = (trim($url->activeFilter(), "/") == ADMIN_URI_FILTER);
        }

        /*
         |  PLUGIN :: INIT IF INSTALLED
         |  @since  0.1.0
         */
        public function installed(){
            if(file_exists($this->filenameDb)){
                if(!defined("PAW_DESIGNER")){
                    define("PAW_DESIGNER", "paw.designer");
                    define("PAW_DESIGNER_PATH", PATH_PLUGINS . basename(__DIR__) . "/");
                    define("PAW_DESIGNER_DOMAIN", DOMAIN_PLUGINS . basename(__DIR__) . "/");
                    define("PAW_DESIGNER_VERSION", "0.1.0");
                }
                if($this->backend){
                    $this->handle();
                }
                return true;
            }
            return false;
        }

        /*
         |  PLUGIN :: SET THEME
         |  @since  0.1.0
         */
        public function setTheme($theme_id){
            global $url;

            // Validate Theme ID
            if(!file_exists(PATH_THEMES . $theme_id . DS)){
                return false;
            }
            activateTheme($theme_id);
            Redirect::url(HTML_PATH_ADMIN_ROOT . $url->slug());
            die();
        }

        /*
         |  PLUGIN :: GET THEME DATA
         |  @since  0.1.0
         */
        public function getTheme($theme_id){
            if(isset($this->db[$theme_id])){
                return $this->db[$theme_id];
            }
            return false;
        }

        /*
         |  HOOK :: BEFORE SITE LOAD
         |  @since  0.1.0
         */
        public function beforeSiteLoad(){
            global $site, $paw_designer_theme;

            // Load Files
            include_once("system/paw-theme.class.php");
            include_once("system/paw-theme.func.php");

            // Init Theme
            $theme = new PawTheme($site->theme());
            if($theme->isTheme()){
                if(!$theme->getData("paw.designer-force")){
                    require_once("system/paw-designer.func.php");
                }
            }
        }

        /*
         |  HOOK :: BEFORE ADMIN LOAD
         |  @since  0.1.0
         */
        public function beforeAdminLoad(){
            global $url;

            //  Check if the current View is the "paw.designer" page on the backend
            if(strpos($url->slug(), "designer") !== 0){
                return false;
            }
            checkRole(array("admin"));

            // Perpare View
            $split = str_replace("designer", "", trim($url->slug(), "/"));
            if(!empty($split) && $split !== "/"){
                if(isset($_GET["activate"])){
                    return $this->setTheme(trim($split, "/"));
                }
                $this->adminPage = "theme";
                $this->adminQuery = array(
                    "theme" => trim($split, "/")
                );
            } else {
                $this->adminPage = "index";
            }
        }

        /*
         |  HOOK :: ADMIN HEADER
         |  @since  0.1.0
         */
        public function adminHead(){
            $return  = '<script type="text/javascript" src="'.PAW_DESIGNER_DOMAIN.'admin/js/paw.designer.js?ver='.PAW_DESIGNER_VERSION.'"></script>';
            $return .= '<script type="text/javascript" src="'.PAW_DESIGNER_DOMAIN.'admin/js/bootstrap-colorpicker.min.js?ver=3.0.3"></script>';
            $return .= '<script type="text/javascript" src="'.PAW_DESIGNER_DOMAIN.'admin/js/jquery-sortable-lists.min.js?ver=1.4.0"></script>';
            $return .= '<link rel="stylesheet" type="text/css" href="'.PAW_DESIGNER_DOMAIN.'admin/css/paw.designer.css?ver='.PAW_DESIGNER_VERSION.'" />';
            $return .= '<link rel="stylesheet" type="text/css" href="'.PAW_DESIGNER_DOMAIN.'admin/css/bootstrap-colorpicker.min.css?ver=3.0.3" />';
            return $return;
        }

        /*
         |  HOOK :: ADMIN SIDEBAR
         |  @since  0.1.0
         */
        public function adminSidebar(){
            return '<a href="' . HTML_PATH_ADMIN_ROOT . 'designer" class="nav-link"><span class="oi oi-brush"></span>Theme Designer</a>';
        }

        /*
         |  HOOK :: BEFORE ADMIN CONTENT
         |  @info   Fetch the HTML content, to inject the paw.designer page.
         |  @since  0.1.0
         */
        public function adminBodyBegin(){
            if(!$this->backend || !$this->adminPage){
                return false;
            }
            ob_start();
        }

        /*
         |  HOOK :: AFTER ADMIN CONTENT
         |  @info   Handle the HTML content, to inject the paw.designer page.
         |  @since  0.1.0
         */
        public function adminBodyEnd(){
            if(!$this->backend || !$this->adminPage){
                return false;
            }
            $content = ob_get_contents();
            ob_end_clean();

            // paw.Designer Content
            ob_start();
            if($this->adminPage == "theme"){
                include_once("system/paw-designer.func.php");
                include_once("system/paw-option.class.php");
                include_once("system/paw-theme.class.php");
                include_once("system/paw-theme.func.php");
                $theme = new PawTheme($this->adminQuery["theme"], true);
            }
            include(PAW_DESIGNER_PATH . "admin/{$this->adminPage}.php");
            $add = ob_get_contents();
            ob_end_clean();

            // Inject Code
            $regexp = "#(\<div class=\"col-lg-10 pt-3 pb-1 h-100\"\>)(.*?)(\<\/div\>)#s";
            $content = preg_replace($regexp, "$1{$add}$3", $content);
            print($content);
        }

        /*
         |  RENDER :: BUILD MENU ITEM
         |  @since  0.1.0
         */
        public function buildMenuItem($slug, $item){
            global $L, $pages, $categories;
            if(empty($slug) || !isset($item["title"])){
                return false;
            }

            // Handle
            if(isset($item["type"]) && $item["type"] == "category"){
                if(!isset($categories->db[$slug])){
                    return false;
                }
                $category = new Category($slug);

                if(empty($item["title"])){
                    $item["title"] = $category->name();
                }
            } else if(isset($item["type"]) && $item["type"] == "page"){
                if(!$pages->exists($slug)){
                    return false;
                }
                $page = new Page($slug);

                if(empty($item["title"])){
                    $item["title"] = $page->title();
                }
            } else {
                $item["type"] = "link";
                if(empty($item["title"])){
                    return false;
                }
            }

            // Generate ID
            $id = preg_replace("#[^a-z0-9_-]#", "", $slug);
            if(empty($id)){
                $id = preg_replace("#[^a-z0-9_-]#", "", strtolower($item["title"]));
            }

            // Render
            ob_start();
            ?>
                <div class="card w-50 paw-menu-card">
                    <div class="card-header sortable-handle">
                        <div class="card-title"><?php echo $item["title"]; ?></div>
                        <div class="card-toggle prevent-sortable" data-toggle="collapse" data-target="#<?php echo $id; ?>"><span class="oi oi-caret-bottom prevent-sortable"></span></div>
                    </div>
                    <div id="<?php echo $id; ?>" class="collapse">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="menu-title-<?php echo $id; ?>"><?php $L->p("pd-admin-033"); ?></label>
                                <input type="text" id="menu-title-<?php echo $id; ?>" class="form-control" name="name" value="<?php echo $item["title"]; ?>" placeholder="<?php $L->p("pd-admin-033"); ?>" />
                            </div>
                            <?php
                                if($item["type"] == "page"){
                                    ?>
                                        <div class="form-group">
                                            <label for="menu-slug-<?php echo $id; ?>"><?php $L->p("pd-admin-047"); ?></label>
                                            <input type="text" id="menu-slug-<?php echo $id; ?>" class="form-control" name="slug" value="<?php echo $slug; ?>" placeholder="<?php $L->p("pd-admin-047"); ?>" />
                                        </div>
                                    <?php
                                } else if($item["type"] == "category"){
                                    ?>
                                        <div class="form-group">
                                            <label for="menu-slug-<?php echo $id; ?>"><?php $L->p("pd-admin-047"); ?></label>
                                            <input type="text" id="menu-slug-<?php echo $id; ?>" class="form-control" name="slug" value="<?php echo $slug; ?>" placeholder="<?php $L->p("pd-admin-047"); ?>" />
                                        </div>
                                    <?php
                                } else {
                                    ?>
                                        <div class="form-group">
                                            <label for="menu-slug-<?php echo $id; ?>"><?php $L->p("pd-admin-047"); ?></label>
                                            <input type="text" id="menu-slug-<?php echo $id; ?>" class="form-control" name="slug" value="<?php echo $slug; ?>" placeholder="<?php $L->p("pd-admin-047"); ?>" />
                                        </div>
                                    <?php
                                }
                            ?>
                            <input type="hidden" name="type" value="<?php echo $item["type"]; ?>" />
                            <button class="btn btn-sm btn-danger" data-handle="delete-menu"><?php $L->p("pd-admin-048"); ?></button>
                        </div>
                    </div>
                </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
