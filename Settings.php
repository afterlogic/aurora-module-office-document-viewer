<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\OfficeDocumentViewer;

use Aurora\System\SettingsProperty;

/**
 * @property bool $Disabled
 * @property bool $IncludeInMobile
 * @property array $ExtensionsToView
 * @property string $ViewerUrl
 */

class Settings extends \Aurora\System\Module\Settings
{
    protected function initDefaults()
    {
        $this->aContainer = [
            "Disabled" => new SettingsProperty(
                false,
                "bool",
                null,
                "Setting to true disables the module",
            ),
            "IncludeInMobile" => new SettingsProperty(
                true,
                "bool",
                null,
                "If true, the module is used in mobile version of the interface",
            ),
            "ExtensionsToView" => new SettingsProperty(
                [
                    "doc",
                    "docx",
                    "docm",
                    "dotm",
                    "dotx",
                    "xlsx",
                    "xlsb",
                    "xls",
                    "xlsm",
                    "pptx",
                    "ppsx",
                    "ppt",
                    "pps",
                    "pptm",
                    "potm",
                    "ppam",
                    "potx",
                    "ppsm",
                    "odt",
                    "odx"
                ],
                "array",
                null,
                "Defines a list of file types which can be viewed by external web service",
            ),
            "ViewerUrl" => new SettingsProperty(
                "https://view.officeapps.live.com/op/view.aspx?src=",
                "string",
                null,
                "URL of external web service used for viewing files",
            ),
        ];
    }
}
