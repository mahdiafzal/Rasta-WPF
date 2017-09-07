function CcontextMenu()
{
	return CcontextMenu.fn.init();
}
CcontextMenu.fn=CcontextMenu.prototype=
{
	target  : '' ,
	lable:
	{
		confirm:'تأیید',
		cancel:'انصراف',
		msg1:'آیا قصد به اشتراک گذاشتن این محتوا را دارید؟',
		
	},
	menuContent: '',
	param:null,
	init:function()
		{
			if(typeof $.facebox != 'function')
			{
				$('head').append('<link href="/lib/facebox/v2/facebox.css" media="screen" rel="stylesheet" type="text/css" />');
				$('head').append('<script src="/lib/facebox/v2/facebox.js" type="text/javascript"></script>');
			}
			this.bind();
			return this;
		},

	bind:function()
		{
			$(document)
				.bind("contextmenu",
					  function(event)
					  {
						  CcontextMenu.fn.target = $(event.target);
						  
						  if(CcontextMenu.fn.validate(event.target))
						  {
							CcontextMenu.fn
								.showConfirmBox()
								.bindConfirm()
								return false;

						  }
						  return true;
					  })
		},
	validate:function(target)
		{
			var aelem	= ($(target).is('a'))?$(target):$(target).parents('a');
			if( !aelem.length )	return false;
			this.href	= aelem.attr('href');
			if(!(/\/id\/\d+[\#\/]/.test(this.href)) && !(/\/id\/\d+$/.test(this.href)) ) return false;
			this.fetchData();
			switch(this.module+'.'+this.controller+'.'+this.action)
			{
				//case 'usermanager.frmregister.index':
				case 'usermanager.frmgroupregister.index':
				case 'rtcmanager.frmregister.index':
				case 'dashboard.gallery.frmregister':
				case 'dashboard.menu.frmregister':
				//case 'dashboard.page.frmedit':
				case 'dashboard.scenario.frmedit':
				case 'dashboard.link.frmedit':
				case 'dandelion.management.frmregister':
				case 'dashboard.manual.frmregister':
				case 'portlet.management.frmportlet':
				case 'portlet.management.frmcontroller':
				case 'skiner.skin.frmregister':
				case 'skiner.gallery.frmregister':
				return this;
			}


//			var ignorepat	= [/^\#/]
//			for(i=0; i<ignorepat.length; i++)
//				if(ignorepat[i].test(this.href))	return false;
			
			return false;
		},
	fetchData:function()
		{
			this.href	= this.href.replace(/\#.*/, '');
			var data	= this.href.split(/\//);
			this.module	= data[1];
			this.controller	= data[2];
			this.action	= data[3];
			this.unic	= '';
			for(i=2; i<data.length; i++)
				if(data[i]=='id')	this.unic	= data[i+1];
			return this;
		},
	showConfirmBox:function()
		{
			var msg	= '\
<div id="shaingConfirm" style="direction:rtl;font-family:tahoma;text-align:right;">\
'+this.lable.msg1+'<br /><br />\
<span class="art-button-wrapper ">\
	<span class="l"> </span>\
	<span class="r"> </span>\
	<a id="confirm_share_"  class="btn art-button" href="#" >'+this.lable.confirm+'</a>\
</span>\
<span class="art-button-wrapper ">\
	<span class="l"> </span>\
	<span class="r"> </span>\
	<a class="btn art-button" onclick="$.facebox.close(); return false;" >'+this.lable.cancel+'</a>\
</span>\
<br /><br />\
</div>';
			$.facebox(msg);
			return this;
		},
	confirm:function()
		{
			sharePopupWin = window.open('/godpanel/share/index?path='+this.module+'.'+this.controller+'.'+this.action+'&id='+this.unic,
						'NewShare',
						"location=1,status=1,scrollbars=1,resizable=1,width=1003,height=500"
						);
			sharePopupWin.moveTo(150,50);
			$.facebox.close();
			return this;
		},
	bindConfirm:function()
		{
			
			$('#confirm_share_').click(function(){CcontextMenu.fn.confirm(); return false;});
			return this;
		},
};
$(function(){
CcontextMenu();
		   });