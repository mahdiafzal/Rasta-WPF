function CcontextMenu()
{
	return CcontextMenu.fn.init();
}
CcontextMenu.fn=CcontextMenu.prototype=
{
	target  : '' ,
	lable:
	{
		ImageManagerimg:['اضافه کردن عکس','حذف عکس'],
		ImageManager:['اضافه کردن عکس','حذف عکس'],
		rtc:['ویرایش متن', 'حذف متن'],
		menu:['ویرایش منو', 'حذف منو'],
		createmenuli:['ویرایش زیرمنو', 'حذف زیرمنو'],
		headerMenu:['اضافه كردن منو', 'حذف منو'],
		gallery:['ویرایش آلبوم','حذف آلبوم']
	},
	menuContent: '',
	param:null,
	init:function()
		{
			this.bind();
			return this;
		},
	bind:function()
		{
			$_(document)
				.bind("contextmenu",
					  function(event)
					  {
						  CcontextMenu.fn.target = $_(event.target);
						  if(CcontextMenu.fn.validate(event.target))
						  {
							CcontextMenu.fn
								.setOptions(event.target)
								.enableOptions(event.target)
								.setPosition(event)
								.getMenu();
						  }
						  return false;
					  })
				.bind('click',
					function()
					{
						$_('#contextMenu').css('display', 'none');
					});
			$_('#contextMenu li')
				.bind('click',
					function()
					{
						if($_(this).find('span:eq(0)').is('.ui-state-disabled')) return this;
						CcontextMenu.fn[this.id]();
						return this;
					});
		},
	validate:function(target)
		{
			forbid=[
					'#verticalPanel',
					'#toolbar'
					];
			for(i=0; i<forbid.length; i++)
			{
				if($_(target).is(forbid[i])) return false;
				if($_(target).parents(forbid[i]).length) return false;
			}
			return this;
			
		},
	setOptions:function(target)
		{
			spTargets=[
						'[type="rtc"]',
						'[type="gallery"]',
						'[type="menu"]',
						'#ImageManager img',
						'#ImageManager',
						'.createmenu li'
						];
			$_('#contextMenu').contents('li:eq(4)')
			.filter(function()
			{
				if($_('.headerMenu').css("display")== "none")
				{
					$_(this)
						.attr('id', 'addHeaderMenu')
						.find('a')
						.text(CcontextMenu.fn.lable['headerMenu'][0])					
				}else{
					$_(this)
						.attr('id', 'removeHeaderMenu')
						.find('a')
						.text(CcontextMenu.fn.lable['headerMenu'][1])	
				}
			});

			$_('#contextMenu').contents('li:eq(0), li:eq(1)')
										.addClass('disabled')
										.find('span:eq(0)')
											.addClass('ui-state-disabled')	
									
			for(i=0; i<spTargets.length; i++)
			{
						
				if(	$_(target).is(spTargets[i]) ||
					$_(target).parents(spTargets[i]).length)
				{
					
					if(! this.target.is(spTargets[i]) ) this.target = this.target.parents(spTargets[i]);
					
					tragetName	= spTargets[i].replace(/^[\.\#]/,'');
					tragetName	= tragetName.replace(/[\-\s]+/g,'');
					if(this.target.is('[type]')) tragetName	= this.target.attr('type');
					$_('#contextMenu').contents('li:eq(0), li:eq(1)')
									.filter(function()
									{
											$_(this)
												.attr('id', tragetName+$_(this).index())
												.find('a')
												.text(CcontextMenu.fn.lable[tragetName][$_(this).index()])
									});
					return this;
				}
			}
			return this;
		},
	enableOptions:function(target)
		{
			spTargets=[
						'[type="rtc"]',
						'[type="gallery"]',
						'[type="menu"]',
						'#ImageManager',
						'#ImageManager img',
						'.createmenu li'
						];
			enableOptions={
					'0':[0,1],
					'1':[0,1],
					'2':[0,1],
					'3':[0],
					'4':[0,1],
					'5':[1]
			};
			for(i=0; i<spTargets.length; i++)
			{
				
				if(	$_(target).is(spTargets[i]) ||
					$_(target).parents(spTargets[i]).length)
				{
					$_.each(enableOptions[i], 
						function()
						{
							$_('#contextMenu').contents('li').eq(this)
												.removeClass('disabled')
											.find('span:eq(0)')
												.removeClass('ui-state-disabled')
						});
				}
			}
			return this;
		},
	setPosition:function(event)
		{
			mouseX = event.pageX;
			mouseY = event.pageY;
			$_('#contextMenu').css({
								  'left': mouseX-50+"px",
								  'top': mouseY+"px"
								  });
			
			return this;
		},
	getMenu:function()
		{
			
			$_('#contextMenu').slideDown();
			return this;
		},
	rtc0:function()
		{
			var unicID = this.target.attr('unic');
				Crtc.fn.editButtonHandle(unicID);
			return this;
		},
	removeBlock:function()
		{
			$_(this).remove();
			Cbreedable.fn.refreshSections();
		},
	rtc1:function()
		{
			this.target.fadeOut("slow",CcontextMenu.fn.removeBlock);
			return this;
		},
	gallery0:function()
		{
			var unicID = this.target.attr('unic');
			$_('#Gallerylist').find('a[galleryid='+unicID+']').prevAll('a:last').click()
			return this;
		},
	gallery1:function()
		{
			return this.rtc1();
		},
	ImageManagerimg0:function()
		{
			Cgallery.fn.openImageManager_multipleFiles();
			return this;
		},
	ImageManager0:function()
		{
			return this.ImageManagerimg0();
		},
	ImageManagerimg1:function()
		{
			this.target.fadeOut("slow",function(){$_(this).remove()});
			return this;
		},
	ImageManager1:function()
		{
			return this.ImageManagerimg1();
		},
	menu0:function()
		{
			var unicID = this.target.attr('unic');
				$_('#menulist').find('a[menuid='+unicID+']').prevAll('a:last').click()
				verticalPanel.fn.open(1);
				$_('#accorContentButton').find('a:eq(2)').click()
			return this;
		},
	menu1:function()
		{
			this.target.fadeOut("slow",CcontextMenu.fn.removeBlock);
			return this;
		},
	addHeaderMenu:function()
		{
			$_('body')
				.find('.headerMenu')
				.fadeIn("slow",function(){$_(this).css("display","block")});
			return this;
		},
	removeHeaderMenu:function()
		{
			$_('body')
				.find('.headerMenu')
				.fadeOut("slow",function(){$_(this).css("display","none")});
			return this;
		},
	createmenuli0:function()
		{
			return this;
		},
	createmenuli1:function()
		{
			this.target.first().fadeOut("slow",function(){$_(this).remove()});
			return this;
		},
	ShowPropertiesPanel:function()
		{
			verticalPanel.fn.open(0);
			return this;
		},		
	ShowContentPanel:function()
		{
			verticalPanel.fn.open(1);
			return this;
		},
	ShowThemePanel:function()
		{
			verticalPanel.fn.open(2);
			return this;
		},	
	ShowPagesPanel:function()
		{
			verticalPanel.fn.open(3);
			return this;
		},
	FileManager:function()
		{
			Ctoolbar.fn.fileManager();
			return this;
		},
	SaveThisPage:function()
		{
			Ctoolbar.fn.savePage();
		}
};
