/*
 |  paw.Designer - A advanced Theme Engine for Bludit
 |  @file       ./admin/js/paw.designer.js
 |  @author     SamBrishes <sam@pytes.net>
 |  @version    0.1.0
 |
 |  @website    https://github.com/pytesNET/paw.designer
 |  @license    X11 / MIT License
 |  @copyright  Copyright © 2018 - 2019 SamBrishes, pytesNET <info@pytes.net>
 */
;(function(root){
    "use strict";
    var w = root, d = root.document;

    /*
     |  HELPER :: LOOP
     |  @since  0.1.0
     */
    var each = function(elements, callback){
        if(elements instanceof HTMLElement){
            callback.call(elements, elements);
        } else if(elements.length && elements.length > 0){
            for(var l = elements.length, i = 0; i < l; i++){
                callback.call(elements[i], elements[i], i);
            }
        }
    };

    // Ready?
    d.addEventListener("DOMContentLoaded", function(){
        "use strict";

        /*
         |  PRE-SELECT MENU
         |  @since  0.1.0
         */
        each(document.querySelectorAll("[data-handle='tabs']"), function(){
            var hash = window.location.hash,
                tabs = this.querySelectorAll("li > a");

            if(tabs.length > 0){
                for(var l = tabs.length, i = 0; i < l; i++){
                    if(tabs[i].getAttribute("href") == hash || (hash.length <= 1 && i == 0)){
                        tabs[i].click();
                        break;
                    }
                }
            }
        });

        /*
         |  MENUS :: SORTABLE
         |  @since  0.1.0
         |  @note   The jQuery SortableList Plugin has been modified!
         */
        var sortableMenu = jQuery("ul.sortable[data-handle='sortable']").sortableLists({
            as: "html",
            handle: ".sortable-handle",
            listSelector: "ul",
            listsClass: "sortable",
            ignoreClass: "prevent-sortable",
            hintClass: "paw-menu-item-hint",
            placeholderClass: "paw-menu-item-placeholder",
            isAllowed: function(current, hint, target){
                return true;
            },
            insertZonePlus: true
        });

        /*
         |  MENUS :: CHANGE MENU
         |  @since  0.1.0
         */
        jQuery("#paw-designer-changer").click(function(event){
            event.preventDefault();

            // Build Link
            var link = w.location.origin + w.location.pathname
                     + "?menu=" + jQuery(this).parent().children("select").val() + "#menus";
            w.location.href = link;
        });

        /*
         |  MENUS :: UPDATE MENU
         |  @since  0.1.0
         */
        jQuery("#paw-designer-updater").click(function(event){
            var nonce = d.querySelector("#paw-designer-ajax-nonce"),
                theme = d.querySelector("#theme-id");
            if(!nonce || !theme){
                return false;
            }

            // Store new Menu
            var list = sortableMenu.sortableListsToHierarchy();
            jQuery.post(HTML_PATH_ADMIN_ROOT + "ajax/paw-designer", {
                action: "menu",
                query: {
                    "theme-id": theme.value,
                    "menu-id": sortableMenu.attr("data-menu"),
                    "menu": list
                },
                nonce: nonce.value
            }, function(data){
                if(data.status == "error"){
                    d.querySelector("#alert").className = "alert alert-danger";
                    showAlert(data.error);
                } else {
                    d.querySelector("#alert").className = "alert alert-success";
                    showAlert(data.success);
                }
            }, "json");
        });

        /*
         |  MENUS :: ADD ITEM
         |  @since  0.1.0
         */
        jQuery("[data-handle='menu']").submit(function(event){
            event.preventDefault();
            if(!this.hasAttribute("data-menu")){
                return false;
            }

            // Validate Items
            var name = this.querySelector("[name='name']"),
                type = this.querySelector("[name='type']"),
                slug = this.querySelector("[name='slug']"),
                nonce = d.querySelector("#paw-designer-ajax-nonce");
            if(!name || !type || !slug || !nonce){
                return false;
            }

            // Render Items
            jQuery.getJSON(HTML_PATH_ADMIN_ROOT + "ajax/paw-designer", {
                action: "build",
                query: {
                    title: name.value,
                    type: type.value,
                    slug: slug.value
                },
                nonce: nonce.value
            }, function(data){
                if(!data.status){
                    return false;
                }

                // Oopsie
                if(data.status === "error"){
                    d.querySelector("#alert").className = "alert alert-danger";
                    showAlert(data.error);
                    return false;
                }

                // Add Item
                jQuery("<li></li>", {
                    id: data.query.slug.replace(/[^a-z0-9_-]/g, "")
                }).html(data.content.trim()).appendTo("#paw-designer-menu-holder");

                // YES!!!
                d.querySelector("#alert").className = "alert alert-success";
                showAlert(data.success);
            });
        });

        /*
         |  MENUS :: REMOVE ITEM
         |  @since  0.1.0
         */
        jQuery("#paw-designer-menu-holder").on("click", "button[data-handle='delete-menu']", function(event){
            event.preventDefault();
            jQuery(this).parent().parent().parent().remove();
        });

        /*
         |  FIELD :: COLOR
         |  @since  0.1.0
         */
        jQuery("[data-handle='color']").colorpicker({
            format:             "auto",
            autoInputFallback:  false
        });

        /*
         |  FIELD :: PAGES
         |  @since  0.1.0
         */
        each(d.querySelectorAll("[data-handle='pages']"), function(PagesElement){
            var PagesAJAX, PagesList;
            jQuery(PagesElement).autoComplete({
                minChars: 1,
                source: function(term, response){
                    try{ PagesAJAX.abort(); } catch(e){ }
                    PagesAJAX = jQuery.getJSON(HTML_PATH_ADMIN_ROOT + "ajax/get-published", {query: term}, function(data){
                        PagesList = data;

                        term = term.toLowerCase();
                        var matches = [];
                        for(var title in data){
                            if(~title.toLowerCase().indexOf(term)){
                                matches.push(title);
                            }
                        }
                        response(matches);
                    });
                },
                onSelect: function(element, term, item){
                    d.querySelector(PagesElement.getAttribute("data-target")).value = PagesList[term];
                }
            });
        });

        /*
         |  FIELD :: MEDIA UPLOADER
         |  @since  0.1.0
         */
        each(d.querySelectorAll("[data-handle='media']"), function(element){
            this.addEventListener("click", function(event){
                event.preventDefault();
                jQuery("#upload-media-modal").attr("data-target", "#" + this.id);
                jQuery("#upload-media-modal").modal("show");
            });
        });
        if(d.querySelector("#upload-media-modal")){
            d.querySelector("#upload-media-modal").addEventListener("change", function(){
                d.querySelector("#upload-media-progressbar").style.width = "1%";

                // AJAX Data
                var formData = new FormData(d.querySelector("#upload-media-form"));
        		formData.append("tokenCSRF", d.querySelector("#nonce").value);
                jQuery.ajax({
                    url: HTML_PATH_ADMIN_ROOT + "ajax/upload-images",
                    type: "POST",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    xhr: function(){
                        var xhr = jQuery.ajaxSettings.xhr();
                        if(!xhr.upload){
                            return xhr;
                        }
                        xhr.upload.addEventListener("progress", function(event){
                            if(event.lengthComputable){
                                var percent = (event.loaded / event.total) * 100;
                                d.querySelector("#upload-media-progressbar").style.width = percent + "%";
                            }
                        }, false);
                        return xhr;
                    }
                }).done(function(data, status){
                    var img = d.createElement("IMG");
                        img.src = DOMAIN_UPLOADS + data.filename;

                    var target = d.querySelector(d.querySelector("#upload-media-modal").getAttribute("data-target"));
                    target.parentElement.querySelector("[data-media='value']").value = DOMAIN_UPLOADS + data.filename;
                    target.parentElement.querySelector("[data-media='preview']").innerHTML = img.outerHTML;
                    target.parentElement.querySelector("[data-media='preview']").className += " changed";
                    jQuery("#upload-media-modal").modal("hide");
                });
            });
        }
    });
}(window));
