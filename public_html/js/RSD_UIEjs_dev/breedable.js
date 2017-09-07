function Cbreedable()
{
	return Cbreedable.fn.init();
}

Cbreedable.fn=Cbreedable.prototype=
{
	allDraggable: ['.RTCtitle, .Gallerytitle, .menutitle', '.pagetitle, .extlinktitle, .scenariotitle'],
	allDroppable: ['.rasta-section', '#createmenu ul', '.headerMenu'],
	init:function()
			{
				$var.secBuffer = [];
				$_('[section]').each(function()
							{
								$_(this).addClass('rasta-section').addClass('section-'+$_(this).attr('section'));
							});
				setTimeout('Cbreedable.fn.setBreedables().reloadBlockSetting().refreshSections();',2000);			
				return this/*.setBreedables().reloadBlockSetting().refreshSections()*/;
			},
	
	refreshSections:function()
			{
				$_('body').find('.rasta-section')
							.each(function()
								{
									if(!$_(this).children().length) 
									{
										var sId = $_(this).attr('class').match(/section\-\d/)[0].replace('section-', '');
										$_(this).append('<div id="contentplaceholder" style="widows:100%; height:50px;"><center><span style="font-size:25px;font-weight:bold;color:#cccccc;">SECTION&nbsp;&nbsp;'+sId+'</span></center></div>');
									}
								})
							.find('#contentplaceholder')
							.each(function()
								{
									if($_(this).siblings().length) $_(this).remove();	
								});
				return this;
			},
	reloadBlockSetting:function()
			{
				var uiData = {
					axis: 'y',
					helper: 'clone',
					placeholder: 'ui-state-highlight rasta-placeholder'
				}
				$_('body').find('.rasta-section')
					.sortable(uiData)
					.find('[class$=header]')
					.each(function()
					{
						if(!$_(this).parents().is('.rasta-block-header'))
							$_(this)
								.addClass('rasta-block-header')
								.unbind('dblclick')
								.dblclick(function()
									{
										$_(this).next().slideToggle("slow");
									});
					});
				return this;
			},
	setBreedables:function()
			{
				if($_('#createmenu:visible').length)
				{
					this.menuBreeding();
				}
				else
				{
					this.pageBreeding().mlMenuBreeding();
				}
				
				return this;
			},
	pageBreeding:function()
			{
				var dropData ={
					accept:this.allDraggable[0],
					activate:this.dropMouseBind,
					drop:this.breedToPage,
					out: function(event, ui) { $_('body').find('.placeholder').remove(); }

				};
				var dragData ={
					stop:this.dropMouseUnbind
				};
				return this
					.destroyDraggable(1)
					.draggable(this.allDraggable[0], dragData)
					.droppable(this.allDroppable[0], dropData);
			},
	mlMenuBreeding:function()
			{
				var dropData ={
					accept:'.menutitle',
					drop:this.breedToMlmenu,
					activate:function()
					{
						$_('.headerMenu').contents().css({'visibility':'hidden'});
					},
					deactivate:function()
					{
						$_('.headerMenu').contents().css({'visibility':'visible'});
					}
				};
				var dragData ={
				};
				return this
					.droppable(this.allDroppable[2], dropData);
			},
	menuBreeding:function()
			{
				var dropData ={
					accept: this.allDraggable.join(', '),
					activate:this.dropMouseUnbind
				}
				var dragData ={};
				this
					.destroyDroppable(0)
					.draggable(this.allDraggable.join(', '), dragData)
					.droppable(this.allDroppable[1], dropData);
				$_( ".menutitle" ).draggable( "destroy" );
				return this;
			},
	destroyDraggable:function(i)
			{
				var selector = this.allDraggable[i];
				$_(selector).draggable( "destroy" );
				return this;
			},
	destroyDroppable:function(i)
			{
				var selector = this.allDroppable[i];
				$_(selector).droppable( "destroy" );
				return this;
			},
	dropMouseUnbind:function(event, ui)
			{
				$_('.ui-droppable')
							.unbind('mouseover')
							.unbind('mouseup')
								.contents()
							.unbind('mouseover');
				var ne=$_('body').find('.placeholder').next();
				$var.placeholder= (ne.length)? ne : false;
				$_('body').find('.placeholder').remove();
			},
	dropMouseBind:function(event, ui)
			{
				$_('body').find('.ui-droppable:not(.headerMenu)')
							.unbind('mouseover')
							.bind('mouseover',
								function(event)
								{
									if($_(this).children().length) return false;
									$_('body').find('.placeholder').remove();
									$_(this).append('<div class="placeholder rasta-placeholder" style=""></div>');
								})
						.contents()
								.unbind('mouseover')
								.bind('mouseover',
									function(event)
									{
										$_('body').find('.placeholder').remove();
										$_(this).before('<div class="placeholder rasta-placeholder" style=""></div>');
									});

			},
	refreshPageContent:function(data)
			{
				$var.editedContent = $_('#container').find('*').filter('[unic="'+data.unic+'"]').filter('[type="'+data.type+'"]');
				//$_('#container').find('[type='+data.type+']').filter('[unic='+data.unic+']');
				$var.editedContent.first()
					.each(function()
					{
						var targetSection = $_(this).parents('.rasta-section').attr('class').match(/section\-\d/)[0].replace('section-', '');
						var	successfn2	= function(msg)
							{
									$var.editedContent.each(function()
														{
															$_(this).fadeOut("slow",
																	function()
																	{
																		$_(this).replaceWith( msg ); 
																		$var.breedable.reloadBlockSetting().refreshSections();
																	});
														});
							}; 
						Cbreedable.fn.getPageContent({type:data.type ,section:targetSection ,unic:data.unic, success:successfn2})
					});
			},
	breedToPage:function(event, ui)
			{
				
				var targetSection = $_(this).attr('class').match(/section\-\d/)[0].replace('section-', '');
				var contentType= ui.draggable.parents('.ui-tabs').attr('id').toLowerCase().replace('list', '');
				var contentId= ui.draggable.attr(contentType+'id');
				$var.dropTargetElem	= $_(this);
				var	successfn	= function(msg)
					{
						if($var.placeholder)
						{
							$_(msg).insertBefore($var.placeholder);
							$var.breedable.reloadBlockSetting().refreshSections();
							$var.placeholder.prev().find('.rasta-block-header').next().hide().slideDown(1500)
						}
						else
						{
							$var.dropTargetElem.append(msg)
							$var.breedable.reloadBlockSetting().refreshSections();
							$var.dropTargetElem.find('.rasta-block-header').last().next().hide().slideDown(1500);
						}
					} 

				Cbreedable.fn.getPageContent({type:contentType ,section:targetSection ,unic:contentId, success:successfn})
					
			},
	getPageContent:function(data)
			{
				var bodyId=$_('#container').attr('unic');
				$_.ajax({
						url		: "/admin/ajaxget/gettingdata",
						type	: 'post',
						dataType: 'html',
						data	: {'type': data.type ,'section': data.section ,'body_id': bodyId,'id': data.unic ,'page_id': config.site.pageId},
						success	: data.success
					});	
			},
	breedToMenu:function(event, ui)
			{
				$_(this).find('.sampleLi').remove();
				var contentType= ui.draggable.parents('.ui-tabs').attr('id').toLowerCase().replace('list', '');
				var contentId= ui.draggable.attr(contentType+'id');
				$_(this).append('<li><a url="/'+contentType+'/'+contentId+'"><span>'+ui.draggable.text()+'</span></a></li>');
				return this;
			},
	breedToMlmenu:function(event, ui)
			{
				var contentType= ui.draggable.parents('.ui-tabs').attr('id').toLowerCase().replace('list', '');
				var targetSection='headermenu';
				var contentId= ui.draggable.attr(contentType+'id');
				var bodyId=$_('#container').attr('unic');
							$_.ajax({
									url			: "/admin/ajaxget/gettingdata",
									type		: 'post',
									dataType	: 'html',
									data		: {'type': contentType ,'section': targetSection ,'body_id': bodyId,'id': contentId ,'page_id': config.site.pageId},
									success		: function(msg)
													{
														$_('.headerMenu').html('<div id="menu"><ul class="menu">'+msg+'</ul></div>').attr('unic',contentId);
														if($_('#pagesttingPanel').find('input[name=mlmskin]').val().length<3)
																	$_('#pagesttingPanel').find('input[name=mlmskin]').val('4.1');
													} 
								});	
							return this;
			},
	draggable:function(selector, data)
			{
				uiData = {
						appendTo: "body",
						helper: 'clone'
				}
				if(typeof data=='object') $_.extend(true, uiData, data);
				$_(selector).draggable(uiData);
				return this;
			},
	droppable:function(selector, data)
			{
				uiData = {
					activeClass: "ui-state-default",
					hoverClass: "ui-state-error",
					accept: ":not(.ui-sortable-helper)",
					drop: this.breedToMenu
				}
				if(typeof data=='object') $_.extend(uiData, data);
				$_(selector).droppable(uiData);
				return this;
			},
	sortableCreateMenu:function(selector)
			{
				var uiData = {
					axis: 'y',
					containment:'parent',
					placeholder: 'ui-state-highlight rasta-placeholder'
				}
				if(typeof selector=='undefined') selector= '#createmenu';
				$_(selector).find('ul')
					.sortable(uiData)
					.disableSelection();
				return this;
			}
};
