'use strict';

module.exports = function (oAppData) {
	var
		App = require('%PathToCoreWebclientModule%/js/App.js'),
				
		CAbstractFileModel = require('%PathToCoreWebclientModule%/js/models/CAbstractFileModel.js')
	;
	
	if (App.getUserRole() === Enums.UserRole.NormalUser)
	{
		return {
			start: function () {
				CAbstractFileModel.addViewExtensions(['doc', 'docx', 'docm', 'dotm', 'dotx', 'xlsx', 'xlsb', 'xls', 'xlsm', 'pptx', 'ppsx', 'ppt', 'pps', 'pptm', 'potm', 'ppam', 'potx', 'ppsm']);
			}
		};
	}
	
	return null;
};
