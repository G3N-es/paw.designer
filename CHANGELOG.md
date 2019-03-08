CHANGELOG
===========

Version 0.1.1 - Alpha
---------------------
-   Add: The new menu array key `classes` can be used to add additional class names to main item
         element (used within the `PDRenderMenu` class).
-   Add: The new menu array key `target` can be used to change the target attribute on the link
         element (used within the `PDRenderMenu` class).
-   Bugfix: Removed "Static Class Dynamic Method Invocation" on the Configuration Rendering.
            A simple `call_user_func` is used now.
            Thanks to: [#1](https://github.com/pytesNET/paw.designer/issues/1).
-   Bugfix: Static Pathing to the `paw-designer` plugin folder, which didn't worked if the folder
            has been renamed!
            Thanks to: [#1](https://github.com/pytesNET/paw.designer/issues/1).
-   Bugfix: The used design for the switch uses now an custom class name to prevent overwriting of
            bludits new core styling.
-   Bugfix: Wrong Linking on the amdin pages (The current theme has been used)
-   Bugfix: Wrong location string has been shown on active not-supported themes.

Version 0.1.0 - Alpha
---------------------
-   Initial Version
