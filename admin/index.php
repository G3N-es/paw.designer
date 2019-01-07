<?php
/*
 |  paw.Designer - A advanced Theme Engine for Bludit
 |  @file       ./admin/index.php
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
    if(!defined("BLUDIT")){ die("Go directly to Jail. Do not pass Go. Do not collect 200 Cookies!"); }
    global $L;

    // Fetch Themes
    $themes = buildThemes();
    $unavailable = $unsupported = $supported = array();
    foreach($themes as $theme){
        if(!isset($theme["paw.designer"])){
            $unavailable[] = $theme;
            continue;
        }
        if(version_compare($theme["paw.designer"], ">", PAW_DESIGNER_VERSION)){
            $unsupported[] = $theme;
            continue;
        }
        $supported[] = $theme;
    }

    // HELPER :: Render Theme
    function pd_admin_render_theme($theme){
        global $L, $site;
        ?>
            <div class="paw-theme <?php echo ($theme["dirname"] == $site->theme())? "current": ""; ?>">
                <a href="<?php echo HTML_PATH_ADMIN_ROOT; ?>designer/<?php echo $theme["dirname"]; ?>" class="theme-screenshot">
                    <?php
                        if(file_exists(PATH_THEMES . $theme["dirname"] . DS . "screenshot.png")){
                            ?><img src="<?php echo DOMAIN_THEME; ?>screenshot.png" /><?php
                        } else if(file_exists(PATH_THEMES . $theme["dirname"] . DS . "screenshot.jpg")){
                            ?><img src="<?php echo DOMAIN_THEME; ?>screenshot.jpg" /><?php
                        } else if(file_exists(PATH_THEMES . $theme["dirname"] . DS . "screenshot.jpeg")){
                            ?><img src="<?php echo DOMAIN_THEME; ?>screenshot.jpeg" /><?php
                        } else {
                            ?><span class="screenshot-empty"><span class="oi oi-question-mark"></span><?php $L->p("pd-admin-001"); ?></span><?php
                        }
                    ?>
                </a>
                <div class="theme-title"><?php echo $theme["name"]; ?></div>
                <div class="theme-description"><?php
                    if(strlen($theme["description"]) > 105){
                        print(substr($theme["description"], 0, 105) . "...");
                    } else {
                        print($theme["description"]);
                    }
                ?></div>
                <div class="theme-meta row">
                    <div class="col-sm">
                        <?php if($site->theme() == $theme["dirname"]){ ?>
                            <span class="badge badge-dark"><?php $L->p("pd-admin-002"); ?></span>
                        <?php } else { ?>
                            <a href="<?php echo HTML_PATH_ADMIN_ROOT; ?>designer/<?php echo $theme["dirname"]; ?>?activate=true" class="badge badge-success"><?php $L->p("pd-admin-003"); ?></a>
                        <?php } ?>
                    </div>
                    <div class="col-sm text-right">
                        <?php
                            if(isset($theme["website"])){
                                echo '<a href="'.$theme["website"].'" target="_blank" class="badge badge-primary">'.$theme["author"].'</a>';
                            } else {
                                echo '<div class="badge badge-primary">'.$theme["author"].'</div>';
                            }
                        ?>
                        <div class="badge badge-primary"><?php echo $theme["version"]; ?></div>
                    </div>
                </div>
            </div>
        <?php
    }

    // HELPER :: Render Theme List
    function pd_admin_render_theme_list($themes){
        global $L, $site;
        ?>
            <table class="table mt-3 paw-themes-table">
                <thead>
                    <tr>
                        <th width="25%" class="border-bottom-0"><?php $L->p("name"); ?></th>
                        <th width="55%" class="border-bottom-0"><?php $L->p("description"); ?></th>
                        <th width="10%" class="border-bottom-0 text-center"><?php $L->p("version"); ?></th>
                        <th width="10%" class="border-bottom-0 text-center"><?php $L->p("author"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($themes AS $theme){ ?>
                        <tr class="<?php echo ($site->theme() == $theme["dirname"])? "current": ""; ?>">
                            <td class="align-middle">
                                <?php echo $theme["name"]; ?>
                                <div class="mt-1">
                                    <?php if($site->theme() == $theme["dirname"]){ ?>
                                        <b><?php $L->p("pd-admin-001"); ?></b>
                                    <?php } else { ?>
                                        <a href="<?php echo HTML_PATH_ADMIN_ROOT; ?>designer/<?php echo $theme["dirname"]; ?>?activate=true"><?php $L->p("pd-admin-003"); ?></a>
                                    <?php } ?>
                                </div>
                            </td>
                            <td class="align-middle"><?php echo $theme["description"]; ?></td>
                            <td class="align-middle text-center"><?php echo $theme["version"]; ?></td>
                            <td class="align-middle text-center"><?php
                                if(isset($theme["website"])){
                                    echo '<a href="'.$theme["website"].'" target="_blank">'.$theme["author"].'</a>';
                                } else {
                                    echo $theme["author"];
                                }
                            ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php
    }

    // RENDER :: Title
    echo Bootstrap::pageTitle(array("title" => "Theme Designer", "icon" => "brush"));

    // RENDER :: Supported Themes
    echo '<h3 class="paw-page-title">'.$L->g("pd-admin-004").'</h3>';
    if(count($supported) > 0){
        ?>
            <div class="paw-themes">
                <?php
                    foreach($supported AS $theme){
                        pd_admin_render_theme($theme);
                    }
                ?>
            </div>
        <?php
    } else {
        ?>
            <p class="paw-status status-info">
                <?php $L->p("pd-admin-005"); ?>
            </p>
        <?php
    }

    // RENDER :: Unsupported Themes
    if(count($unsupported) > 0){
        ?>
            <h3 class="paw-page-title"><?php $L->p("pd-admin-006"); ?></h3>
            <p class="paw-status status-error">
                <?php $L->p("pd-admin-007"); ?>
            </p>
        <?php
        pd_admin_render_theme_list($unsupported);
    }

    // RENDER :: Unavailable Themes
    if(count($unavailable) > 0){
        ?>
            <h3 class="paw-page-title"><?php $L->p("pd-admin-008"); ?></h3>
            <p class="paw-status">
                <?php $L->p("pd-admin-009"); ?>
            </p>
        <?php
        pd_admin_render_theme_list($unavailable);
    }
