<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\OfficeDocumentViewer;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @property Settings $oModuleSettings
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
    /**
     * Initializes module.
     *
     * @ignore
     */
    public function init()
    {
        $this->subscribeEvent('System::RunEntry::before', array($this, 'onBeforeFileViewEntry'), 5);
        $this->subscribeEvent('Files::GetFile', array($this, 'onGetFile'), 10);
    }

    /**
     * @return Module
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * @return Module
     */
    public static function Decorator()
    {
        return parent::Decorator();
    }

    /**
     * @return Settings
     */
    public function getModuleSettings()
    {
        return $this->oModuleSettings;
    }

    public function GetSettings()
    {
        \Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);

        return array(
            'ExtensionsToView' => $this->oModuleSettings->ExtensionsToView
        );
    }

    /**
     * @param string $sFileName = ''
     * @return bool
     */
    protected function isOfficeDocument($sFileName = '')
    {
        $sExtensions = implode('|', $this->oModuleSettings->ExtensionsToView);
        return !!preg_match('/\.(' . $sExtensions . ')$/', strtolower(trim($sFileName)));
    }

    /**
     *
     * @param array $aArguments
     * @param array $aResult
     */
    public function onBeforeFileViewEntry(&$aArguments, &$aResult)
    {
        $aEntries = [
            'download-file',
            'file-cache',
            'mail-attachment'

        ];
        if (in_array($aArguments['EntryName'], $aEntries)) {
            $sEntry = (string) \Aurora\System\Router::getItemByIndex(0, '');
            $sHash = (string) \Aurora\System\Router::getItemByIndex(1, '');
            $sAction = (string) \Aurora\System\Router::getItemByIndex(2, '');

            $aValues = \Aurora\System\Api::DecodeKeyValues($sHash);

            $sFileName = isset($aValues['FileName']) ? urldecode($aValues['FileName']) : '';
            if (empty($sFileName)) {
                $sFileName = isset($aValues['Name']) ? urldecode($aValues['Name']) : '';
            }

            if ($this->isOfficeDocument($sFileName) && $sAction === 'view') {
                if (!isset($aValues['AuthToken'])) {
                    $aValues['AuthToken'] = \Aurora\System\Api::UserSession()->Set(
                        array(
                            'token' => 'auth',
                            'id' => \Aurora\System\Api::getAuthenticatedUserId()
                        ),
                        time(),
                        time() + 60 * 5 // 5 min
                    );

                    $sHash = \Aurora\System\Api::EncodeKeyValues($aValues);

                    // 'https://view.officeapps.live.com/op/view.aspx?src=';
                    // 'https://view.officeapps.live.com/op/embed.aspx?src=';
                    // 'https://docs.google.com/viewer?embedded=true&url=';

                    $sViewerUrl = $this->oModuleSettings->ViewerUrl;
                    if (!empty($sViewerUrl)) {
                        if (isset($_SERVER['HTTP_REFERER'])) {
                            $sHost = $_SERVER['HTTP_REFERER'];
                        } else {
                            $sHost = $_SERVER['HTTP_HOST'];
                        }
                        \header('Location: ' . $sViewerUrl . urlencode($sHost . '?' . $sEntry . '/' . $sHash . '/' . $sAction . '/' . time()));
                    }
                } else {
                    $sAuthToken = isset($aValues['AuthToken']) ? $aValues['AuthToken'] : null;
                    if ($sAuthToken) {
                        \Aurora\System\Api::setAuthToken($sAuthToken);
                        \Aurora\System\Api::setUserId(
                            \Aurora\System\Api::getAuthenticatedUserId($sAuthToken)
                        );
                    }
                }
            }
        }
    }

    /**
     *
     * @param array $aArguments
     * @param array $aResult
     */
    public function onGetFile(&$aArguments, &$aResult)
    {
        if ($this->isOfficeDocument($aArguments['Name'])) {
            $aArguments['NoRedirect'] = true;
        }
    }
}
