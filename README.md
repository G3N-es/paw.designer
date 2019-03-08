paw.designer - A Theme extension 4 Bludit
=========================================
**paw.designer** is a theme environment extension AND plugin for the Bludit content management
system. The theme extension is just a single file, which theme developers need to include within
their `init.php` file, the plugin is completely optional and allows users to configure
**paw.designer** -compatible themes without touching code from the backend.


Documentation
-------------
The documentation is available on [GitHubs Wiki Pages](https://github.com/pytesNET/paw.designer/wiki)!

Instructions - Users
--------------------
Plugin users just need to include the **paw.designer** folder to their `bludit-plugins` directory.
Enable the plugin on the "Bludit Administration" and visit the new Menu Item "Theme Designer".
**NOTE:** You need to include `paw-designer` compatible themes to use the functionallity of this
plugin, of course.

### Compatible Themes
The following themes supports the **paw.designer** environment AND plugin:

- [fur.zerendo](https://github.com/pytesNET/fur.zerendo) - A Blogger Template

----------------------

Instructions - Developers
-------------------------
As theme developer you need to include the `system/paw-designer.func.php` file inside your template
and load them within your `init.php` file. You can use the following snippet:

```php
if(!function_exists("pd_load_theme")){
    // Include the paw-designer.func.php file from your template directory
    require_once("paw-designer.func.php");
}
pd_load_theme();
```

The configurations and menu settings SHOULD be available in the `theme.php` file within the root
directory of your template. Read more about the [Customizer Functions](https://github.com/pytesNET/paw.designer/wiki/Customizer-Functions).
You can also move or rename the default `theme.json` file using the [`paw.designer-file` key](https://github.com/pytesNET/paw.designer/wiki/Plugin-Functions#pawdesigner-file)
inside your `metadata.json` file.

### Support the paw.designer Plugin
You also **MUST** define the [`paw.designer` key](https://github.com/pytesNET/paw.designer/wiki/Plugin-Functions#imporant)
within your `metadata.json` file, if you want to support the **paw.designer** plugin too. You can
also configure the options and menus on the **paw.designer** backend using the respective
[Plugin Functions](https://github.com/pytesNET/paw.designer/wiki/Plugin-Functions#pd_configure_optionkey-config).

Copyright & License
-------------------
Published under the MIT-License; Copyright © 2018 - 2019 SamBrishes, pytesNET
