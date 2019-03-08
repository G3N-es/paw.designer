<?php
/*
 |  paw.Designer - A advanced Theme Engine for Bludit
 |  @file       ./admin/theme.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.1 [0.1.0] - Alpha
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }
    global $L, $site, $security, $categories;

    // RENDER :: Title
    $title = "<a href=\"".HTML_PATH_ADMIN_ROOT."/designer\" class=\"paw-link\">Theme Designer</a>";
    echo Bootstrap::pageTitle(array("title" => $title." &rsaquo; ".$theme->getTheme(), "icon" => "brush"));

    // Theme not Found
    if(!$theme->isTheme()){
        ?>
            <h1 class="text-center"><?php $L->p("pd-admin-010"); ?></h1>
            <h2 class="text-center"><?php $L->p("pd-admin-011"); ?></h2>
        <?php
        return;
    }
?>
<ul class="nav nav-tabs" role="tablist" data-handle="tabs">
    <li class="nav-item">
        <a class="nav-link" id="details-tab" data-toggle="tab" href="#details" role="tab"><?php $L->p("pd-admin-012"); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="options-tab" data-toggle="tab" href="#options" role="tab"><?php $L->p("pd-admin-013"); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="menus-tab" data-toggle="tab" href="#menus" role="tab"><?php $L->p("pd-admin-014"); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="editor-tab" data-toggle="tab" href="#editor" role="tab"><?php $L->p("pd-admin-015"); ?></a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane show" id="details" role="tabpanel">
        <div class="paw-info">
            <div class="row">
                <div class="col-sm">
                    <h3>
                        <?php echo $theme->getData("name", $theme->getData("dirname")); ?>
                        <?php if($site->theme() == $theme->getTheme()){ ?>
                            <span class="badge badge-dark" style="font-size:12px;font-weight:normal;vertical-align:top;margin-top:8px;"><?php $L->p("pd-admin-016"); ?></span>
                        <?php } ?>
                    </h3>
                    <dl style="margin-top:1.5em;">
                        <dt><?php $L->p("pd-admin-017"); ?></dt>
                        <dd><?php echo $theme->getData("version", "0.0.0"); ?> (<?php $L->p("pd-admin-018"); ?>: <?php echo $theme->getData("releaseDate", "<i>" . $L->g("pd-admin-019") . "</i>"); ?>)</dd>

                        <dt><?php $L->p("pd-admin-020"); ?></dt>
                        <dd><?php echo $theme->getData("author", "<i>" . $L->g("pd-admin-019") . "</i>"); ?><?php
                            $meta = array();
                            if(($mail = filter_var($theme->getData("email"), FILTER_SANITIZE_EMAIL)) !== false){
                                $meta[] = "<a href=\"mailto:{$mail}\" target=\"_blank\">".$L->g("pd-admin-021")."</a>";
                            }
                            if(($website = filter_var($theme->getData("website"), FILTER_SANITIZE_URL)) !== false){
                                $meta[] = "<a href=\"{$website}\" target=\"_blank\">".$L->g("pd-admin-022")."</a>";
                            }
                            if(!empty($meta)){
                                echo " (" . implode(", ", $meta) . ")";
                            }
                        ?></dd>

                        <dt><?php $L->p("pd-admin-023"); ?></dt>
                        <dd><?php echo $theme->getData("license", "<i>" . $L->g("pd-admin-019") . "</i>"); ?></dd>

                        <dt><?php $L->p("pd-admin-024"); ?></dt>
                        <dd>Bludit v.<?php echo $theme->getData("compatible", "0.0.0"); ?><br />paw.designer v.<?php echo $theme->getData("paw.designer", "0.0.0"); ?></dd>

                        <?php
                            $desc = $theme->getData("description", false);
                            if(!empty($desc)){
                                ?>
                                    <dt><?php $L->p("pd-admin-025"); ?></dt>
                                    <dd><?php echo $desc; ?></dd>
                                <?php
                            }

                            $desc = $theme->getData("notes", false);
                            if(!empty($desc)){
                                ?>
                                    <dt><?php $L->p("pd-admin-026"); ?></dt>
                                    <dd><?php echo $desc; ?></dd>
                                <?php
                            }
                        ?>
                    </dl>
                </div>
                <div class="col-sm">
                    <div class="card">
                        <?php
                            if(file_exists(PATH_THEMES . $theme->getTheme() . DS . "screenshot.png")){
                                ?><img src="<?php echo DOMAIN . HTML_PATH_THEMES . $theme->getTheme(); ?>/screenshot.png" /><?php
                            } else if(file_exists(PATH_THEMES . $theme->getTheme() . DS . "screenshot.jpg")){
                                ?><img src="<?php echo DOMAIN . HTML_PATH_THEMES . $theme->getTheme(); ?>/screenshot.jpg" /><?php
                            } else if(file_exists(PATH_THEMES . $theme->getTheme() . DS . "screenshot.jpeg")){
                                ?><img src="<?php echo DOMAIN . HTML_PATH_THEMES . $theme->getTheme(); ?>/screenshot.jpeg" /><?php
                            } else {
                                ?><span class="screenshot-empty"><span class="oi oi-question-mark"></span><?php $L->p("pd-admin-001"); ?></span><?php
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane show" id="options" role="tabpanel">
        <?php
            if(!empty($theme->getOptions())){
                ?>
                    <form method="post" action="<?php echo HTML_PATH_ADMIN_ROOT; ?>designer/<?php echo $theme->getTheme(); ?>#options">
                        <div class="card" style="margin: 1.5rem 0;">
                            <div class="card-body">
                                <input type="hidden" id="theme-id" name="query[theme-id]" value="<?php echo $theme->getTheme(); ?>" />
                                <input type="hidden" id="nonce" name="nonce" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <input type="hidden" id="tokenCSRF" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <button class="btn btn-primary" name="action" value="options"><?php $L->p("pd-admin-027"); ?></button>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-2 pb-2 border-bottom text-uppercase"><?php $L->p("pd-admin-028"); ?></h6>
                        <?php
                            foreach($theme->getOptions() AS $key => $config){
                                ?>
                                    <div class="form-group row">
                                        <label for="<?php echo $key; ?>" class="col-sm-2 col-form-label"><?php echo $config["title"]; ?></label>
                                    	<div class="col-sm-10">
                                            <?php
                                                if(method_exists("PawOption", $config["type"])){
                                                    echo call_user_func(array("PawOption", $config["type"]), $key, $config);
                                                } else {
                                                    echo PawOption::text($key, $config);
                                                }
                                            ?>

                                            <?php if(isset($config["description"])){ ?>
                            		            <small class="form-text text-muted"><?php echo $config["description"]; ?></small>
                                            <?php } ?>
                                    	</div>
                                    </div>
                                <?php
                            }
                        ?>
                    </form>
                <?php
            } else {
                ?>
                    <div class="paw-status status-error">
                        <?php $L->p("pd-admin-29"); ?>
                    </div>
                <?php
            }
        ?>
    </div>

    <div class="tab-pane show" id="menus" role="tabpanel">
        <?php
            $menu = isset($_GET["menu"])? $_GET["menu"]: NULL;
            $menus = array_keys($theme->getMenus());

            if(!empty($menus)){
                if(!in_array($menu, $menus)){
                    $menu = $menus[0];
                }
                ?>
                    <div class="paw-menus">
                        <div class="card paw-change-menus">
                            <div class="card-body">
                                <select id="paw-change-menu" class="form-control custom-select">
                                    <?php foreach($theme->getMenus() AS $slug => $menu_data){ ?>
                                        <option value="<?php echo $slug; ?>" <?php pd_selected($slug, $menu); ?>><?php echo $menu_data["title"]; ?> (<?php echo $slug; ?>)</option>
                                    <?php } ?>
                                </select>
                                <input id="paw-designer-ajax-nonce" type="hidden" name="nonce" value="<?php echo $security->getTokenCSRF(); ?>" />
                                <button id="paw-designer-changer" class="btn btn-dark"><?php $L->p("pd-admin-030"); ?></button>
                                <button id="paw-designer-updater" class="btn btn-success" style="float:right"><?php $L->p("pd-admin-031"); ?></button>
                            </div>
                        </div>

                        <div class="row paw-create-menus">
                            <div class="col-9">
                                <div class="paw-menu-editor">
                                    <ul id="paw-designer-menu-holder" class="sortable" data-handle="sortable" data-menu="<?php echo $menu; ?>">
                                        <?php
                                            function pd_admin_render_menu_list($list){
                                                global $PawDesignerPlugin;
                                                foreach($list AS $slug => $item){
                                                    if(($content = $PawDesignerPlugin->buildMenuItem($slug, $item)) === false){
                                                        continue;
                                                    }
                                                    $id = preg_replace("#[^a-z0-9_-]#", "", $slug);

                                                    ?><li id="menu-item-<?php echo $id; ?>"><?php
                                                        print($content);
                                                        if(isset($item["children"])){
                                                            ?><ul class="sortable"><?php
                                                                pd_admin_render_menu_list($item["children"]);
                                                            ?></ul><?php
                                                        }
                                                    ?></li><?php
                                                }
                                            }
                                            pd_admin_render_menu_list($theme->getMenu($menu));
                                        ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="accordion" id="menu-creation-toolkit">
                                    <div class="card">
                                        <div class="card-header" data-toggle="collapse" data-target="#menu-page-links"><?php $L->p("pd-admin-032"); ?></div>
                                        <div id="menu-page-links" class="collapse show" data-parent="#menu-creation-toolkit">
                                            <div class="card-body">
                                                <form method="post" data-handle="menu" data-menu="add-page">
                                                    <div class="form-group">
                                                        <label><?php $L->p("pd-admin-033"); ?></label>
                                                        <input type="text" class="form-control form-control-sm" name="name" value="" placeholder="<?php $L->p("pd-admin-033"); ?>" />
                                                        <small class="form-text text-muted"><?php $L->p("pd-admin-034"); ?></small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><?php $L->p("pd-admin-035"); ?></label>
                                                        <input type="hidden" id="paw-menu-select-page" name="slug" value="" />
                                                        <input type="text" data-handle="pages" data-target="#paw-menu-select-page" class="form-control form-control-sm" value="" placeholder="<?php $L->p("pd-admin-036"); ?>" />
                                                    </div>
                                                    <input type="hidden" name="type" value="page" />
                                                    <button type="submit" class="btn btn-sm btn-secondary"><?php $L->p("pd-admin-037"); ?></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header" data-toggle="collapse" data-target="#menu-category-links"><?php $L->p("pd-admin-038"); ?></div>
                                        <div id="menu-category-links" class="collapse" data-parent="#menu-creation-toolkit">
                                            <div class="card-body">
                                                <form method="post" data-handle="menu" data-menu="add-category">
                                                    <div class="form-group">
                                                        <label><?php $L->p("pd-admin-033"); ?></label>
                                                        <input type="text" class="form-control form-control-sm" name="name" value="" placeholder="<?php $L->p("pd-admin-033"); ?>" />
                                                        <small class="form-text text-muted"><?php $L->p("pd-admin-039"); ?></small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><?php $L->p("pd-admin-040"); ?></label>
                                                        <select class="custom-select custom-select-sm" name="slug">
                                                            <?php foreach($categories->getDB() AS $slug => $cat){ ?>
                                                                <option value="<?php echo $slug; ?>"><?php echo $cat["name"]; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="type" value="category" />
                                                    <button type="submit" class="btn btn-sm btn-secondary"><?php $L->p("pd-admin-037"); ?></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header" data-toggle="collapse" data-target="#menu-individual-links"><?php $L->p("pd-admin-041"); ?></div>
                                        <div id="menu-individual-links" class="collapse" data-parent="#menu-creation-toolkit">
                                            <div class="card-body">
                                                <form method="post" data-handle="menu" data-menu="add-url">
                                                    <div class="form-group">
                                                        <label><?php $L->p("pd-admin-037"); ?></label>
                                                        <input type="text" class="form-control form-control-sm" name="name" value="" placeholder="<?php $L->p("pd-admin-037"); ?>" />
                                                    </div>
                                                    <div class="form-group">
                                                        <label><?php $L->p("pd-admin-042"); ?></label>
                                                        <input type="text" class="form-control form-control-sm" name="slug" value="" placeholder="<?php $L->p("pd-admin-042"); ?>" />
                                                    </div>
                                                    <input type="hidden" name="type" value="link" />
                                                    <button type="submit" class="btn btn-sm btn-secondary"><?php $L->p("pd-admin-037"); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
            } else {
                ?>
                    <div class="paw-status status-error">
                        <?php $L->p("pd-admin-043"); ?>
                    </div>
                <?php
            }
        ?>
    </div>

    <div class="tab-pane show" id="editor" role="tabpanel">
        <div class="paw-status status-info">
            <?php $L->p("pd-admin-044"); ?>
        </div>
    </div>
</div>

<div id="upload-media-modal" class="modal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col p-3">
                        <h3 class="mt-2 mb-3"><?php $L->p("pd-admin-045"); ?></h3>

                        <form id="upload-media-form" name="upload-media-form" enctype="multipart/form-data">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="upload-media-file" name="bluditInputFiles[]" />
                                <label class="custom-file-label" for="upload-media-file"><?php $L->p("pd-admin-046"); ?></label>
                            </div>
                        </form>

                        <div class="progress mt-2">
                            <div id="upload-media-progressbar" class="progress-bar bg-primary" role="progressbar" style="width:0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
