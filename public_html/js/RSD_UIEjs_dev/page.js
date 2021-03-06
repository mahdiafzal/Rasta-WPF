function Cpage()
{
	return Cpage.fn.init();
}
Cpage.fn=Cpage.prototype=
{
	buffer: null,
	init:function()
			{
				this.setPageList().setListTools();
				return this;
			},
	New:function()
			{
				var pageButtonSet = '<div class="rasta-buttonset">'
									+	'<a>ویرایش عنوان صفحه</a>'
									+	'<a>مشاهده در همین پنجره</a>'
									+	'<a>مشاهده در پنجره جدید</a>'
									+	'<a class="pagetitle" pageid="">'
									+		'<input type="text" /></a>'
									+'</div>';
				$_('#pagelist').find('div#tabs-1').prepend(pageButtonSet);
				var newPageElem = $_('#pagelist .rasta-buttonset:first').children('a');
				Cpage.fn.setPageButtonSet(newPageElem.last())
						.allButtonUnbind([0,1,2])
						.setSaveButton(newPageElem.first())
						.saveButtonBind(newPageElem.first())
						.setCancelButton(newPageElem.first())
						.deleteButtonBind(newPageElem.first());
				newPageElem.find('input').focus();
				return this;
			},
	newBind:function()
			{
				$_('#pagelistpanel .listtools .ui-button:eq(0)')
					.click(function()
								{
									Cpage.fn.New();
								});
				return this;
			},
	setPageList:function()
			{
				$_('#pagelist .pagetitle')
					.each(function()
					{
						Cpage.fn.setPageButtonSet(this);
					});
				return this;
			},
	setListTools:function()
			{
				setListTools('#pagelistpanel .listtools', this.New )
				return this;
			},
	setPageButtonSet:function(elem)
			{
						$_(elem)
							.button()
							.dblclick(function() 
							{
								window.open('/page/'+$_(this).attr('pageid'));
							})
						.prev('a')
							.button( {
								text: false,
								icons: {
									primary: "ui-icon-newwin"
								}
							})
							.click(function() {
											Cpage.fn.newwinOpenpage(this);
							})
						.prev('a')
							.button( {
								text: false,
								icons: {
									primary: "ui-icon-extlink"
								}
							})
							.click(function() {
								Cpage.fn.samewinOpenpage(this);
							})
						.prev('a')
							.button( {
								text: false,
								icons: {
									primary: "ui-icon-wrench"
								}
							})
							.click(function() {
								Cpage.fn.editButtonHandle(this);
							})
						.parent()
							.buttonset();
				return this;
			},
	newwinOpenpage:function(elem)
			{
					var Thispage = $_(elem).nextAll('a:last');
					mywindow = window.open(Thispage.attr('pageid'), 'page'+Thispage.attr('pageid'));
			},
	samewinOpenpage:function(elem)
			{
					var Thispage = $_(elem).nextAll('a:last');
					window.location.assign(Thispage.attr('pageid'))
			},
	refreshButtonSetting:function()
			{
				$_('#pagelist .pagetitle')
					.each(function()
					{
						$_(this).prevAll('a:last')
							.button("option", {
								label: "ویرایش عنوان صفحه",
								icons: {
									primary: "ui-icon-wrench"
								}
							})
						.next('a')
							.button("option", {
								label: "مشاهده در همین پنجره",
								icons: {
									primary: "ui-icon-extlink"
								}
							});
					});
				return this;
			},
	refreshButtonBind:function()
			{
				this.allButtonUnbind([0,1,2,3]).newBind();
				$_('#pagelist .pagetitle')
					.each(function()
					{
						$_(this)
						.prev('a')
							.click(function() {
								Cpage.fn.newwinOpenpage(this);
							})
						.prev('a')
							.click(function() {
								Cpage.fn.samewinOpenpage(this);
							})
						.prev('a')
							.click(function() {
								Cpage.fn.editButtonHandle(this);
							});
					});
			},
			
	allButtonUnbind:function(elemsindex)
			{
				$_('#pagelistpanel .listtools .ui-button').unbind('click');
				$_('#pagelist .rasta-buttonset')
						.each(function()
						{
							for(i=0; i<elemsindex.length; i++)
							{
								$_(this).contents('a').eq(elemsindex[i])
											.unbind('click');
							}
						});
				return this;
			},
	setSaveButton:function(elem)
			{
				var options = {
						label: "ذخیره",
						icons: {
							primary: "ui-icon-disk"
								}
						};
				$_(elem).button( "option", options ); 
				return this;
			},
	saveButtonBind:function(elem)
			{
				$_(elem)
					.click(function()
					{
						Cpage.fn.editButtonHandle(this);
					});
				return this;
			},
	setCancelButton:function(elem)
			{
				var options = {
						label: "انصراف",
						icons: {
							primary: "ui-icon-close"
								}
						};
				$_(elem).next()
					.button( "option", options )
				return this;
			},
	cancelButtonBind:function(elem)
			{
				$_(elem).next()
					.click(function()
						{
								Cpage.fn.refreshButtonSetting()
										.refreshButtonBind();
								$_(this).nextAll('a:last').find('.ui-button-text')
									.html(Cpage.fn.buffer);
						});
				return this;
			},
	deleteButtonBind:function(elem)
			{
				$_(elem).next()
					.click(function()
						{
								
								$_(this).parent().remove();
								Cpage.fn.refreshButtonSetting()
										.refreshButtonBind();
						});
				return this;
			},
	editButtonHandle:function(elem)
			{
					var options;
					var thispage = $_(elem).nextAll('a:last');
					if ( $_(elem).find('.ui-icon-wrench').length ) 
					{
						this.allButtonUnbind([0,1])
							.setSaveButton(elem)
							.saveButtonBind(elem)
							.setCancelButton(elem)
							.cancelButtonBind(elem);
						this.buffer = thispage.text();
						thispage.find('.ui-button-text').html('<input type="text" value="'+this.buffer+'" />');
					}
					else if($_(elem).find('.ui-icon-disk').length)
					{
						if ($_('#pagelist .rasta-buttonset').find('input').val()!='')
						{
							this.refreshButtonSetting()
								.refreshButtonBind();
								$_('#pagelist .rasta-buttonset:first').find('.ui-state-focus').removeClass('ui-state-focus');
							pageid	= $_(elem).nextAll('a:last').attr('pageid');
							title 	= thispage.find('.ui-button-text input').val()
							if(! pageid)
							{
								Cpage.fn.savePage(title);
								
							}
							else
							{
								Cpage.fn.replacePage(title,pageid);
							}
						}
						else
						{
							$var.alert('عنوان صفحه نمی تواند خالی باشد');
						}
					}
					
			},
	savePage:function(title)
			{
				$_.ajax
					({
						url			: '/admin/ajaxset/savepage',
						type		: 'post',
						dataType	: 'json',
						data		: {'title':title},
						success		: function(msg){ $var.paging.refresh('page',1); $var.alert(msg[1]);} 
					
					});
			},
	replacePage:function(title,pageid)
			{
				$_.ajax
					({
						url			: '/admin/ajaxset/replacepage',
						type		: 'post',
						dataType	: 'json',
						data		: {'title':title,'pageid':pageid},
						success		: function(msg){$var.paging.refresh('page',1); $var.alert(msg[1]);} 
					
					});
			}
};