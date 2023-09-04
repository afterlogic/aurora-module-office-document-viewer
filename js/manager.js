'use strict';

module.exports = function (oAppData) {
	var
		moment = require('moment'),
		App = require('%PathToCoreWebclientModule%/js/App.js'),
		Types = require('%PathToCoreWebclientModule%/js/utils/Types.js'),
				
		CAbstractFileModel = require('%PathToCoreWebclientModule%/js/models/CAbstractFileModel.js')
	;
	
	if (App.isUserNormalOrTenant())
	{
		return {
			start: function () {
				var aExtensionsToView = oAppData['%ModuleName%'] ? oAppData['%ModuleName%']['ExtensionsToView'] : [];
				aExtensionsToView = aExtensionsToView.map((item) => { return Types.pString(item).toLowerCase() });
				CAbstractFileModel.addViewExtensions(aExtensionsToView);

				App.subscribeEvent('AbstractFileModel::FileView::before', function (oParams) {
					if (oParams['sUrl'])
					{
						oParams['sUrl'] += '&' + moment().unix() ;
					}
				});
			}
		};
	}
	
	return null;
};
