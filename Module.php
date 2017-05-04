<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\OfficeDocumentViewer;

/**
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	/***** private functions *****/
	/**
	 * Initializes module.
	 * 
	 * @ignore
	 */
	public function init()
	{
		$this->subscribeEvent('Files::download-file-entry::before', array($this, 'onBeforeFileViewEntry'));
		$this->subscribeEvent('Core::file-cache-entry::before', array($this, 'onBeforeFileViewEntry'));
		$this->subscribeEvent('Mail::mail-attachment-entry::before', array($this, 'onBeforeFileViewEntry'));
	}
	
	/**
	 * @param string $sFileName = ''
	 * @return bool
	 */
	protected function isOfficeDocument($sFileName = '')
	{
		return !!preg_match('/\.(doc|docx|docm|dotm|dotx|xlsx|xlsb|xls|xlsm|pptx|ppsx|ppt|pps|pptm|potm|ppam|potx|ppsm)$/', strtolower(trim($sFileName)));
	}	
	
	/**
	 * 
	 * @param type $aArguments
	 * @param type $aResult
	 */
	public function onBeforeFileViewEntry(&$aArguments, &$aResult)
	{
		$sEntry = (string) \Aurora\System\Application::GetPathItemByIndex(0, '');
		$sHash = (string) \Aurora\System\Application::GetPathItemByIndex(1, '');
		$sAction = (string) \Aurora\System\Application::GetPathItemByIndex(2, '');

		$aValues = \Aurora\System\Api::DecodeKeyValues($sHash);
		
		$sFileName = isset($aValues['Name']) ? urldecode($aValues['Name']) : '';
		if (empty($sFileName))
		{
			$sFileName = isset($aValues['FileName']) ? urldecode($aValues['FileName']) : '';
		}

		if ($this->isOfficeDocument($sFileName) && $sAction === 'view' && !isset($aValues['AuthToken']))
		{
			$aValues['AuthToken'] = \Aurora\System\Api::UserSession()->Set(
				array(
					'token' => 'auth',
					'id' => \Aurora\System\Api::getAuthenticatedUserId()
				),
				time() + 60 * 5 // 5 min
			);			
			
			$sHash = \Aurora\System\Api::EncodeKeyValues($aValues);
			
			\header('Location: https://docs.google.com/viewer?embedded=true&url=' . $_SERVER['HTTP_REFERER'] . '?' . $sEntry .'/' . $sHash . '/' . $sAction);
		}
		$sAuthToken = isset($aValues['AuthToken']) ? $aValues['AuthToken'] : null;
		if (isset($sAuthToken))
		{
			\Aurora\System\Api::setAuthToken($sAuthToken);
			\Aurora\System\Api::setUserId(
				\Aurora\System\Api::getAuthenticatedUserId($sAuthToken)
			);
		}			
	}
}	
