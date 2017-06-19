# TYPO3 Custom Content Element Support

The aim of this module is to make it easier to register custom content elements in TYPO3. To do so, you need:

* A custom content element registered (in `ext_localconf.php` ) with an Extbase controller/action combo
* A custom content type registered (in `ext_tables.php`) to kick off the configured Extbase action

## Quick start:

Define your custom content elements in your extension. Place a config file at 
```
EXT:myext/Configuration/CustomContent/General.php
```

The file should look like this: 
```php
<?php

return [
    'my_cce_type' => [
        'action' => 'myAction',
        'noCache' => false,
        'flexform' => "FILE:EXT:{$_EXTKEY}/Resources/Configuration/FlexForms/myaction_flexform.xml", // optional
        'ui' => '
            CType;;4;button;1-1-1,
            pi_flexform,
            --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,
            --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility,
            --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access,
            --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.extended,
            tx_gridelements_container,
            tx_gridelements_columns',
        'label' => 'A special content element to render a list of wodgets'
    ],
];
```

Then, register your custom content elements. 

In `localconf_context.php`:

```php
\Castiron\CustomContent\Utility\CustomContentElementUtility::addCustomContentElements($_EXTKEY, 'MyVendorPrefix');
```

In `ext_tables.php`:

```php
\Castiron\CustomContent\Utility\CustomContentElementUtility::addCustomContentElementTypes($_EXTKEY);
```

Then, you'll need to add an Extbase controller, with an action to match the 'action' param configured for 
 the new element:

```php
EXT:myext/Classes/Controller/GeneralContentController.php
```

You'll now find a new custom content element type in the backend on content elements. The rest is 
 standard Extbase stuff!
