/**
 * @class imex.panel.Home
 * @extends MODx.Panel
 * @param {Object} config An object of options.
 * @xtype imex-panel-home
 */
imex.panel.Home = function(config){

	config = config || {};
	
	this.ident_imp = config.ident || 'imex' + Ext.id();
	this.ident_exp = config.ident || 'imex' + Ext.id();
	
	Ext.apply(config, {
		id: 'imex-panel-cmp',
		border: false,
		baseCls: 'container modx-formpanel',
		baseParams: {
			hideFiles: config.hideFiles || false,
			wctx: MODx.ctx || 'web',
			currentAction: MODx.request.a || 0,
			currentFile: MODx.request.file || ''
		},
		items: [{
			html: '<h2>' + _('imex') + '</h2>',
			border: false,
			cls: 'modx-page-header'
		}, {
			xtype: 'modx-tabs',
			padding: '15px',
			defaults: {
				border: false,
				autoHeight: true
			},
			border: true,
			tabPosition: 'top',
			stateful: true,
			stateId: 'imex-home-tabpanel',
			stateEvents: ['tabchange'],
			getState: function(){
				return {
					activeTab: this.items.indexOf(this.getActiveTab())
				};
			},
			items: [{
				title: _('imex.export'),
				defaults: {
					autoHeight: true
				},
				
				items: [{
					xtype: 'form',
					id: 'imex_export_form',
					border: false,
					labelWidth: 180,
					buttonAlign: 'left',
					
					items: [{
						xtype:'fieldset',
						title: _('imex.export_settings'),
						collapsible: true,
						collapsed: true,
						width: 480,
						defaults: {
							anchor: '0' // leave room for error icon
						},
						items :[{
							xtype: 'modx-field-parent-ch',
							ident: this.ident_exp,
							fieldLabel: _('resource_parent'),
							description: _('imex.parent_resource_help'),
							name: 'parent_exp_combo',
							id: 'modx-resource-parent-cmp' + this.ident_exp,
							value: 'catalog',
							width: 280
						}, {
							xtype: 'modx-combo',
							id: 'imex_combo-config-exp-cmp',
							name: 'config_file',
							editable: false,
							resizable: false,
							url: imex.config.connectorUrl,
							baseParams: {
								action: 'files',
								type: 'config'
							},
							fieldLabel: _('imex.config'),
							value: 'catalog',
							width: 280
						}, {
							xtype: 'hidden',
							name: 'parent_exp',
							value: 'catalog',
							id: 'modx-resource-parent-hidden-cmp' + this.ident_exp
						}, {
							xtype: 'radiogroup',
							fieldLabel: _('imex.file_type'),
							name: 'exp_type',
							autoWidth: true,
							columns: [70, 70, 70],
							items: [{
								checked: true,
								autoWidth: true,
								boxLabel: 'XLS',
								name: 'exp_type',
								inputValue: 'xls'
							},{
								autoWidth: true,
								boxLabel: 'XLSX',
								name: 'exp_type',
								inputValue: 'xlsx'
							}, {
								autoWidth: true,
								boxLabel: 'CSV',
								name: 'exp_type',
								inputValue: 'csv'
							}]
						}]
					}],
					buttons: [{
						type: 'button',
						width: 180,
						text: _('imex.button_export'),
						handler: this.exportDocuments
					}]
				}],
				listeners: {
					activate: function(panel){
					}
				}
			}, {
				title: _('imex.import'),
				defaults: {
					autoHeight: true
				},
				items: [{
					xtype: 'form',
					id: 'imex_import_form',
					border: false,
					labelWidth: 180,
					buttonAlign: 'left',

					items: [{
						xtype:'fieldset',
						title: _('imex.import_settings'),
						collapsible: true,
						collapsed: true,
						width: 500,
						defaults: {
							anchor: '0' // leave room for error icon
						},
						items :[{

							xtype: 'compositefield',
							fieldLabel: _('resource_parent'),
							items: [{
								xtype: 'modx-field-parent-ch',
								id: 'imex_modx-resource-parent-cmp' + this.ident_imp,
								ident: this.ident_imp,
								description: _('imex.parent_resource_help'),
								name: 'parent_res_combo',
								value: 'catalog',
								flex: 1
							}, {
								xtype: 'hidden',
								name: 'parent_res',
								value: 'catalog',
								id: 'modx-resource-parent-hidden-cmp' + this.ident_imp
							}, {
								xtype: 'button',
								name: 'button_clear',
								fieldLabel: '',
								text: '',
								icon: imex.config.assetsUrl + 'img/clear.png',
								tooltip: {
									text: _('imex.clean_parent')
								},
								width: 30,
								handler: this.cleanParent,
								style: {
									paddingLeft: '2px'
								}
							}]
						}, {
							xtype: 'modx-combo',
							id: 'imex_combo-config-cmp',
							name: 'config_file',
							editable: false,
							resizable: false,
							url: imex.config.connectorUrl,
							baseParams: {
								action: 'files',
								type: 'config'
							},
							fieldLabel: _('imex.config'),
							value: 'catalog',
							flex: 1
						}, {
							xtype: 'radiogroup',
							id: 'imex_import-type-cmp',
							fieldLabel: _('imex.imp_type'),
							name: 'imp_type',
							autoWidth: true,
							columns: [100, 100],
							items: [{
								checked: true,
								autoWidth: true,
								boxLabel: _('imex.imp_type_refresh'),
								name: 'imp_type',
								inputValue: 'update'
							}, {
								autoWidth: true,
								boxLabel: _('imex.imp_type_add'),
								name: 'imp_type',
								inputValue: 'add'
							}]
						}]
					}, {
						xtype: 'modx-combo',
						id: 'imex_combo-files-cmp',
						name: 'imp_file',
						editable: false,
						resizable: false,
						url: imex.config.connectorUrl,
						baseParams: {
							action: 'files',
							type: 'import'
						},
						fieldLabel: _('imex.imp_file'),
						value: '',
						width: 280
					}, {
						xtype: 'compositefield',
						//fieldLabel: _('imex.files'),
						items: [{
							xtype: 'hidden'
						}, {
							xtype: 'button',
							name: 'button_upload',
							fieldLabel: '',
							text: '',
							icon: imex.config.assetsUrl + 'img/upload.png',
							tooltip: {
								text: _('imex.upload_files')
							},
							width: 90,
							handler: this.uploadFiles
						}, {
							xtype: 'button',
							name: 'button_delfiles',
							fieldLabel: '',
							text: '',
							icon: imex.config.assetsUrl + 'img/delete.png',
							tooltip: {
								text: _('imex.delete_files')
							},
							width: 90,
							style: {
								position: 'absolute'
							},
							handler: this.deleteFiles
						}, {
							xtype: 'button',
							name: 'button_refreshfiles',
							fieldLabel: '',
							text: '',
							icon: imex.config.assetsUrl + 'img/refresh.png',
							tooltip: {
								text: _('imex.refresh_files')
							},
							width: 90,
							style: {
								position: 'absolute'
							},
							handler: this.refreshFiles
						}]
					}],
					buttons: [{
						type: 'button',
						width: 180,
						text: _('imex.button_import'),
						handler: function(){
							Ext.getCmp('imex-panel-cmp').startConsole();
							Ext.getCmp('imex-panel-cmp').importDocuments();
						}
					}]
				}]
			}]
		}],
		listeners: {
			afterrender: function(panel){
			}
		}
	});
	
	imex.panel.Home.superclass.constructor.call(this, config);
	
};

Ext.extend(imex.panel.Home, MODx.Panel, {
	console: null,
	topic: '/clearcache/',
	register: 'mgr',

	startConsole: function(){
		if (MODx.console == null || MODx.console == undefined) {
		MODx.console = MODx.load({
			xtype: 'modx-console'
			,register: this.register
			,topic: this.topic
		});
        } else {
            MODx.console.setRegister(this.register, this.topic);
        }
		MODx.console.show(Ext.getBody());
	},

	importDocuments: function(skip, total){
		if (typeof(skip) == 'undefined') 
			var skip = 0;
		if (typeof(total) == 'undefined') 
			var total = 0;
		var imp_form = Ext.getCmp('imex_import_form');
		var formValues = imp_form.getForm().getValues();
		
		formValues.skip = skip;
		
		Ext.Ajax.request({
			url: imex.config.connectorUrl,
			params: {
				action: 'import',
				register: this.register,
				topic: this.topic,
				data: Ext.encode(formValues)
			},
			method: 'POST',
			success: function(response, options){
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success == false) {
					Ext.Msg.alert(_('imex.message'), result.message);
					return;
				}
				if (result.object.pos <= result.object.lines_count) {
					Ext.getCmp('imex-panel-cmp').importDocuments(result.object.pos, result.object.lines_count);
				}
				else {
					Ext.getCmp('modx-resource-tree').refresh();
					MODx.clearCache();
				}
			}
		});
	},
	
	exportDocuments: function(btn, e){
		Ext.getCmp('imex_export_form').getEl().mask(_('loading'), 'x-mask-loading');
		var formValues = Ext.getCmp('imex_export_form').getForm().getValues();
		Ext.Ajax.request({
			url: imex.config.connectorUrl,
			params: {
				action: 'export',
				data: Ext.encode(formValues)
			},
			method: 'POST',
			success: function(response, options){
				Ext.getCmp('imex_export_form').getEl().unmask();
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.success == false) {
					Ext.Msg.alert(_('imex.message'), result.message);
				}
				else {
					if (result.object.filename != '') {
						window.location = result.object.filepath;
					}
				}
			}
		});
	},
	
	cleanParent: function(btn, e){
		var imp_form = Ext.getCmp('imex_import_form');
		var formValues = imp_form.getForm().getValues();
		formValues.imp_type = 'clean_parent';
		Ext.Msg.confirm(_('imex.confirm'), _('imex.confirm_clean'), function(e){
			if (e == 'yes') {
				imp_form.getEl().mask(_('loading'), 'x-mask-loading');
				Ext.Ajax.request({
					url: imex.config.connectorUrl,
					params: {
						action: 'import',
						data: Ext.encode(formValues)
					},
					method: 'POST',
					success: function(response, options){
						imp_form.getEl().unmask();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success == false) {
							Ext.Msg.alert(_('imex.message'), result.message);
						}
						else {
							//imp_form.getForm().reset();
							Ext.getCmp('modx-resource-tree').refresh();
							MODx.clearCache();
						}
					}
				});
			}
		}, this);
	},
	
	uploadFiles: function(btn, e){
		if (!this.uploader) {
			this.uploader = new Ext.ux.UploadDialog.Dialog({
				url: MODx.config.connectors_url + 'browser/file.php',
				base_params: {
					action: 'upload',
					path: imex.config.filesImportPath,
					wctx: MODx.ctx || '',
					source: ''
				},
				reset_on_hide: false,
				width: 550,
				cls: 'ext-ux-uploaddialog-dialog modx-upload-window',
				listeners: {
					show: function(){
					},
					uploadsuccess: function(){
						Ext.getCmp('imex_combo-files-cmp').getStore().reload();
					},
					uploaderror: function(){
					},
					uploadfailed: function(){
					}
				}
			});
		}
		this.uploader.show(btn);
	},
	
	deleteFiles: function(btn, e){
		var imp_form = Ext.getCmp('imex_import_form');
		Ext.Msg.confirm(_('imex.confirm'), _('imex.confirm_delete_files'), function(e){
			if (e == 'yes') {
				imp_form.getEl().mask(_('loading'), 'x-mask-loading');
				Ext.Ajax.request({
					url: imex.config.connectorUrl,
					params: {
						action: 'files',
						type: 'delete'
					},
					method: 'POST',
					success: function(response, options){
						imp_form.getEl().unmask();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success == false) {
							Ext.Msg.alert(_('imex.message'), result.message);
						}
						else {
							Ext.getCmp('imex_combo-files-cmp').getStore().reload();
							Ext.getCmp('imex_combo-files-cmp').reset();
						}
					}
				});
			}
		}, this);
	},
	
	refreshFiles: function(btn, e){
		Ext.getCmp('imex_import_form').getEl().mask(_('loading'), 'x-mask-loading');
		Ext.getCmp('imex_combo-config-cmp').getStore().reload();
		Ext.getCmp('imex_combo-files-cmp').getStore().reload();
		setTimeout(function(){
			Ext.getCmp('imex_import_form').getEl().unmask();
		}, 500);
	}
});

Ext.reg('imex-panel-home', imex.panel.Home);

/**
 * ChangeParentField
 */
MODx.ChangeParentField = function(config){
	config = config || {};
	this.ident = config.ident || 'qcr' + Ext.id();
	Ext.applyIf(config, {
		triggerAction: 'all',
		editable: false,
		readOnly: false,
		formpanel: 'modx-panel-resource'
	});
	MODx.ChangeParentField.superclass.constructor.call(this, config);
	this.config = config;
	this.on('click', this.onTriggerClick, this);
	this.addEvents({
		end: true
	});
	this.on('end', this.end, this);
};

Ext.extend(MODx.ChangeParentField, Ext.form.TriggerField, {

	oldValue: false,
	oldDisplayValue: false,
	
	end: function(p){
		var t = Ext.getCmp('modx-resource-tree');
		if (!t) 
			return;
		p.d = p.d || p.v;
		t.removeListener('click', this.handleChangeParent, this);
		t.on('click', t._handleClick, t);
		t.disableHref = false;
		Ext.getCmp('modx-resource-parent-hidden-cmp' + this.ident).setValue(p.v);
		this.setValue(p.d);
		this.oldValue = false;
		//Ext.getCmp(this.config.formpanel).fireEvent('fieldChange');
	},
	
	onTriggerClick: function(){
		if (this.disabled) {
			return false;
		}
		if (this.oldValue) {
			this.fireEvent('end', {
				v: this.oldValue,
				d: this.oldDisplayValue
			});
			return false;
		}
		var t = Ext.getCmp('modx-resource-tree');
		if (!t) {
			var tp = Ext.getCmp('modx-leftbar-tabpanel');
			if (tp) {
				tp.on('tabchange', function(tbp, tab){
					if (tab.id == 'modx-resource-tree-ct') {
						this.disableTreeClick();
					}
				}, this);
				tp.activate('modx-resource-tree-ct');
			}
			return false;
		}
		this.disableTreeClick();
	},
	
	disableTreeClick: function(){
		MODx.debug('Disabling tree click');
		t = Ext.getCmp('modx-resource-tree');
		if (!t) {
			return false;
		}
		this.oldDisplayValue = this.getValue();
		this.oldValue = Ext.getCmp('modx-resource-parent-hidden-cmp' + this.ident).getValue();
		this.setValue(_('resource_parent_select_node'));
		t.expand();
		t.removeListener('click', t._handleClick);
		t.on('click', this.handleChangeParent, this);
		t.disableHref = true;
		return true;
	},
	
	handleChangeParent: function(node, e){
		var t = Ext.getCmp('modx-resource-tree');
		if (!t) {
			return false;
		}
		t.disableHref = true;
		var id = node.id.split('_');
		id = id[1];
		if (id == MODx.request.id) {
			MODx.msg.alert('', _('resource_err_own_parent'));
			return false;
		}
		var ctxf = Ext.getCmp('modx-resource-context-key');
		if (ctxf) {
			var ctxv = ctxf.getValue();
			if (node.attributes && node.attributes.ctx != ctxv) {
				ctxf.setValue(node.attributes.ctx);
			}
		}
		this.fireEvent('end', {
			v: node.attributes.type != 'modContext' ? id : node.attributes.pk,
			d: Ext.util.Format.stripTags(node.text)
		});
		e.preventDefault();
		e.stopEvent();
		return true;
	}
});
Ext.reg('modx-field-parent-ch', MODx.ChangeParentField);

