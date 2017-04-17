<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
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
			
			\header('Location: https://docs.google.com/viewer?url=' . $_SERVER['HTTP_REFERER'] . '?' . $sEntry .'/' . $sHash . '/' . $sAction);
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
