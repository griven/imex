var imex = function(config){
	config = config || {};
	imex.superclass.constructor.call(this, config);
};

Ext.extend(imex, Ext.Component, {
	page: {},
	window: {},
	grid: {},
	tree: {},
	panel: {},
	combo: {},
	tab: {},
	config: {}
});

Ext.reg('imex', imex);

imex = new imex();
