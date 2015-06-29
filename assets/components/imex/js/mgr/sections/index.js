
Ext.onReady(function(){
	MODx.load({
		xtype: 'imex-page-home'
	});
});

imex.page.Home = function(config){
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'imex-panel-home',
			renderTo: 'imex-panel-home-div'
		}]
	});
	imex.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(imex.page.Home, MODx.Component);
Ext.reg('imex-page-home', imex.page.Home);

