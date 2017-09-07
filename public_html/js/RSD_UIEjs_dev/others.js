$_(function() {
	$var.tempSelect = tempSelectFunc;
	setDirButton();
	templateResetButton();
	templateSelectButton();
	pageThemeList();
	mlMenuThemeList();
	vPanelTabs();
	accorMainButtons();
	createMenuBind('#createmenu li a');
	ajaxLoadingAnimation();
	getPageProperties();
	$var.alert = rastaAlert;
	
});
function getPageProperties()
{
	$_.ajax({
			url			: "/admin/ajaxget/getdataofpage",
			type		: 'post',
			dataType	: 'json',
			data		: {'pageID': config.site.pageId},
			success		: function(msg)
							{
								for(attr in msg)
								$_('#pagesttingPanel').find('[name='+attr+']').val(msg[attr]);
								activeDirButton();
							} 
		});	
}
function ajaxLoadingAnimation()
{
	 $_("#loading").bind("ajaxSend", function(){
	   $_(this).show();
	 }).bind("ajaxComplete", function(){
	   $_(this).hide();
	 });	 
}
function rastaAlert(msg, inButton=null)
{
	var _buttons = [
					{
						text: "بستن",
						click: function() { $_(this).dialog("close"); }
					}
				];
	if(inButton!=null)
	{
		_buttons = [
					inButton,
					{
						text: "بستن",
						click: function() { $_(this).dialog("close"); }
					}
				];
	}
	$_('<div id="rastaAlert"></div>')
		.html(msg)
		.dialog({ buttons: _buttons	});
}
function tempSelectFunc(skin)
{
	
	$_('<div id="confirm"></div>')
		.html('برای اعمال تغییرات، این صفحه ذخیره و مجدداً بارگزاری خواهد شد. <br> آیا تأیید می نمایید؟')
		.dialog({
		buttons: [
					{
						text: "تأیید",
						click: function() 
						{ 
							$_('#pagesttingPanel').find('input[name=skin]').val(skin);
							Ctoolbar.fn.saveSuccess=function(msg){
								if(msg[0]==true) window.location = 'http://'+window.location.host+window.location.pathname+'?setskin=unset'; 
								else $var.alert(msg[1]); } ;
							Ctoolbar.fn.savePage();
							$_(this).dialog("close");
						}
					},
					{
						text: "انصراف",
						click: function() { $_(this).dialog("close"); }
					}
				]
		});
}
function pageThemeList()
{
	$_('#pagethemelist').find('.themeIcon')
					.button()
					.click(function()
					{
						if($_(this).index()>4) return false;
						var selectedlink = $_('link[href^=/templates/skin]');
						var themeHref = selectedlink.eq(0).attr('href').split('/');
						themeHref[5]= ($_(this).index()+1);
						$_('#pagesttingPanel').find('input[name=skin]').val(themeHref[4]+'.'+themeHref[5]);
						$_("#loading").fadeIn().delay(4000).fadeOut();
						selectedlink.eq(0).attr('href', themeHref.join('/'))
					});
}

function templateResetButton()
{
	$_('#tempReset').button()
						.click(function()
							{
								$_('#pagesttingPanel').find('input[name=skin]').val('0');
							})
}
function templateSelectButton()
{
	$_('#tempSelect').button()
						.click(function()
							{
								
								if (typeof tempSelectWin =='undefined') tempSelectWin={closed:true};
								if(tempSelectWin.closed==false)
								{
									tempSelectWin.focus();
								}
								else
								{
									tempSelectWin = null;
									tempSelectWin	=	window.open('/skiner/skin/frmlist#fragment-4','tempSelectWin',
																"location=1,status=1,scrollbars=1,resizable=1,width=1003,height=500")
									tempSelectWin.moveTo(150,50);									
								}
							})
}
function mlMenuThemeList()
{
	$_('#mlmthemelist')
				.find('.themeOption')
				.button()
				.click(function()
				{
					if(!$_('.headerMenu:visible').has('div#menu').length) return false;
					var menuThemeNum	=	$_(this).siblings('.themeIcon').attr('Mtempid');
					var menuColorNum	=	$_(this).index();
					var Mselectedlink	= $_('link[href^=/templates/mlmenu/apycom]');
					var MthemeHref 		= Mselectedlink.eq(0).attr('href').split('/');
					MthemeHref[4]		= menuThemeNum;
					MthemeHref[5]		= menuColorNum;
					Mselectedlink.eq(0).attr('href', MthemeHref.join('/'));
					MthemeHref 			= Mselectedlink.eq(1).attr('href').split('/');
					MthemeHref[4]		= menuThemeNum;
					$_("#loading").fadeIn().delay(3000).fadeOut();
					Mselectedlink.eq(1).attr('href', MthemeHref.join('/'))
					$_('#pagesttingPanel').find('input[name=mlmskin]').val(menuThemeNum+'.'+menuColorNum);

				});
}

function vPanelTabs()
{
	$_('#RTClist, #Gallerylist, #pagelist, #menulist, #extlinklist, #mlmthemelist, #pagethemelist, #scenariolist')
			.tabs({
						cookie: {
							expires: 1
							},
						collapsible: false,
						deselectable: true
					});
}
function accorMainButtons()
{
	$_('#accorContentButton, #accorDesignButton, #accorOthersButton')
				.contents('a')
					.each(function()
					{
						$_(this)
							.button( {
									text: false,
									icons: {
										primary: "ui-icon-"+$_(this).attr('icon')
									}
								})
							.click(function()
								{
									$_(this).parent().siblings('div')
											.each(function()
											{
												$_(this).css('display', 'none');
											})
											.eq($_(this).index())
												.css('display', 'block');
								$_(this).addClass('ui-state-highlight')
										.siblings('.ui-state-highlight')
											.removeClass('ui-state-highlight')
										.parents('.ui-accordion-content').prev().find('#listTitle').html('&nbsp;:&nbsp;'+$_(this).text());	
								})
					});
}
function createMenuBind(selector)
{	$_(selector).live('click', function()
					{
						$_(this)
							.parent('li').siblings('li')
								.each(function()
								{
									$_(this).find('div').hide();
								})
						$_(this)
							.parent('li').children('div')
											.toggle()
										.find('div')
											.hide() ;
					})
					.live('dblclick', function()
					{
						var selectedLi=$_(this).parent('li');
						var level=selectedLi.parents('li').size()+1;
						if(!selectedLi.find('div').length & level<3)
						{
								selectedLi
									.contents('a').addClass('parent');
								selectedLi
									.append('<div class="addedsubmenu"><ul><li class="sampleLi"><a href="#" ><span>پیوند یا محتوای آزمایشی</span></a></li></ul></div>')
									.find('.addedsubmenu')
											.show();
								$var.breedable
										.droppable('.addedsubmenu ul')
										.sortableCreateMenu('.addedsubmenu');
								$_('.addedsubmenu')
										.removeClass('addedsubmenu');
						}
						else if(level>=3)
						{
							$var.alert('حداکثر تعداد زیر منوهای مجاز دو سطح می باشد.')
						}
					});
	
}
function setListTools(elem, handler)
{
	$_(elem)
		.contents('a')
			.each(function()
			{
				$_(this).button( {
						text: false,
						icons: {
							primary: "ui-icon-"+$_(this).attr('icon')
						}
					})
					;
			}).first().click(handler);
}
function activeDirButton()
{
	$_( "#dir_radio" ).find('input')
		.removeAttr('checked')
		.filter('[value='+$_('#pagesttingPanel').find('input[name=page_dir]').val()+']')
		.next().click();
}
function setDirButton()
{
	$_( "#dir_radio" )
		.buttonset()
		.find('label')
			.click(function()
			{
				var dir = [$_(this).prev('input').val(), 0];
				$_('#pagesttingPanel').find('input[name=page_dir]').val(dir[0]);
				if(dir[0]==1) dir=['rtl', 'ltr']; else dir=['ltr', 'rtl'];
				$_(document).find('link[href$="'+dir[1]+'.css"]')
					.each(function(){$_(this).attr('href', $_(this).attr('href').replace(dir[1],dir[0]));});
			})
}