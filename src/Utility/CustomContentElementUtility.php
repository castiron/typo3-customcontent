<?php namespace Castiron\CustomContent\Utility;

use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class CustomContentElementUtility
 * @package Castiron\CustomContent\Utility
 */
class CustomContentElementUtility {
    /**
     * Takes a config array like this:
     *
     * $ceConf = array(
     *   'action' => 'sliderGrantees',
     *   'noCache' => false,
     *   'ui' => '
     *   CType;;4;button;1-1-1,
     *   header,
     *   pi_flexform,
     *   --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access, --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.visibility;visibility, --palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access,
     *   --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.extended',
     *   'label' => 'Grantee Slider'
     * )
     *
     * @param string $extKey
     * @param string $ceKey
     * @param array $ceConf
     * @param string $vendorPrefix A vendor prefix for your namespace (like 'Castiron'). You need this if your controller uses a namespaced classname.
     * @param string $customContentElementController
     * @throws \TYPO3\CMS\Core\Error\Exception
     */
    protected static function addCustomContentElement($extKey, $ceKey, $ceConf, $vendorPrefix = '', $customContentElementController = '') {
        if (!$customContentElementController) {
            throw new Exception('You must specify a custom content element controller');
        }

        /**
         * Configure the allowed actions
         */
        $allowedActionConfig = [$customContentElementController => $ceConf['action']];
        if($ceConf['noCache']) {
            $nonCacheableAction = $allowedActionConfig;
        } else {
            $nonCacheableAction = [];
        }
        $cacheableAction = $allowedActionConfig;

        /**
         * Configure the plugin as a content element
         */
        $typeId = static::typeId($extKey, $ceKey);
        ExtensionUtility::configurePlugin(
            ($vendorPrefix ? $vendorPrefix . '.' : '') . $extKey,
            $ceKey,
            $cacheableAction,
            $nonCacheableAction,
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        // Unset default TS config.
        $ts = "
[GLOBAL]
tt_content.${typeId}.10 >
        ";

        ExtensionManagementUtility::addTypoScript($extKey, 'setup', $ts, true);
    }

    /**
     * @param string $extKey
     * @param string $ceKey The content config key. This corresponds to the config file for the elements
     */
    public static function addCustomContentElementTypes($extKey, $ceKey = 'General') {
        foreach (static::getCceConfiguration($extKey, $ceKey) as $ceKey => $ceConf) {
            static::addCustomContentElementType($extKey, $ceKey, $ceConf);
        }
    }

    /**
     * @param $extKey
     * @param $ceKey
     * @return string
     */
    protected static function typeId($extKey, $ceKey) {
        $normalized = static::normalizeExtKey($extKey);
        return "{$normalized}_{$ceKey}";
    }

    /**
     * @param string $extKey
     * @param string $vendorPrefix The vendor prefix for this content element (usually the same prefix
     *                             as your plugin namespace)
     * @param string $ceKey The content config key. This corresponds to the config file for the elements
     */
    public static function addCustomContentElements($extKey, $vendorPrefix = '', $ceKey = 'General') {
        $ceKey = ucfirst($ceKey);
        $customContentElementController = "${ceKey}Content";
        foreach(static::getCceConfiguration($extKey, $ceKey) as $key => $ceConf) {
            static::addCustomContentElement($extKey, $key, $ceConf, $vendorPrefix, $customContentElementController);
        }
    }

    /**
     * For calling from ext_tables.php files
     *
     * @param string $extKey
     * @param string $ceKey
     * @param array $ceConf
     */
    protected static function addCustomContentElementType($extKey, $ceKey, $ceConf) {
        global $TCA;

        $iconPath = $ceConf['iconPath'] ?: 'EXT:core/Resources/Public/Icons/T3Icons/content/content-menu-pages.svg';
        ExtensionUtility::registerPlugin($extKey, $ceKey, $ceConf['label'], $iconPath);

        $typeId = static::typeId($extKey, $ceKey);
        $TCA['tt_content']['types'][$typeId]['showitem'] = $ceConf['ui'];

        /**
         * Add flexform if configured
         */
        if ($ceConf['flexform']) {
            $TCA['tt_content']['columns']['pi_flexform']['config']['ds']["*,{$typeId}"] = $ceConf['flexform'];
        }
    }

    /**
     * @param $extensionName
     * @return string
     */
    protected static function normalizeExtKey($extensionName) {
        $extensionName = strtolower(str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionName))));
        return GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName);
    }

    /**
     * @param $extKey
     * @param $cceKey
     * @return array
     */
    protected static function getCceConfiguration($extKey, $cceKey) {
        $confFile = "EXT:{$extKey}/Configuration/CustomContent/{$cceKey}.php";
        $config = include(GeneralUtility::getFileAbsFileName($confFile));
        return $config;
    }
}
