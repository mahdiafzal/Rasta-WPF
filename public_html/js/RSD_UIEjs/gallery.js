	function Cgallery()
	{
		return Cgallery.fn.init();
	}
	Cgallery.fn=Cgallery.prototype=
	{
		init:function()
				{
					this.getGallerys().setListTools().setDialog();
					return this;
				},
		setListTools:function()
				{
					setListTools('#gallerylistpanel .listtools', this.New )
					return this;
				},
		getGallerys:function()
				{
					Cgallery.fn.setGalleryList();
					return this;
				},			
		setGalleryList:function()
				{
					$_('#Gallerylist .Gallerytitle')
						.each(function()
						{
							Cgallery.fn.setGalleryButtonSet(this);
						});
					return this;
				},
		setGalleryButtonSet:function(elem)
				{
					$_(elem)
						.button()
						.dblclick(function() 
						{
						})
					.prev('a')
						.button( {
							text: false,
							icons: {
								primary: "ui-icon-newwin"
							}
						})
						.click(function() {
							Cgallery.fn.newwinOpen(this);
						})
					.prev('a')
						.button( {
							text: false,
							icons: {
								primary: "ui-icon-wrench"
							}
						})
						.click(function() {
							Cgallery.fn.openDialog(this,'edit');	
						})
					.parent()
						.buttonset();
					return this;
				},
		New:function()
				{
					Cgallery.fn.openDialog(this,'new');
					return this;
				},
		setDialog:function()
				{
					$_('<div dir="rtl" id="GalleryDialog"></div>')
							.html('<br />عنوان آلبوم عکس خود را وارد کنید : '
								  +'<input id="GalleryTitleInput" type="text" value="" />'
								  +'<br /><br />مدیریت عکس ها : <br /><hr />'
								  +'<div style="border:1px #eeeeee solid; min-height:110px; height:auto;" id="ImageManager"></div>')
							.dialog({ 
										width: 600,
										height: 300,
										autoOpen: false,
										resizable: false
										})
											
					/*$_('#ImageManager').dblclick(function(){
						Cgallery.fn.openImageManager_multipleFiles();
					});*/
					$_('#ImageManager img').live("dblclick", function(event){
						Cgallery.fn.addDataToImage(this);
						event.stopImmediatePropagation();
					});
					$_('#addDataToGalleryImagesForm #ImageDataKey').live('keyup', function(event){
						var _DATA = $_($var.LastImageForData).data('metaData');
						var dKey = $_(this).val();
						if(/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(dKey) == false) return;
						if(typeof _DATA[dKey] !='undefined')
							$_('#addDataToGalleryImagesForm #ImageDataValue').val( _DATA[dKey] );
					});
					return this;
				},
		openDialog : function(elem,state)
				{
					if (state=='edit')
					{	
						var title 	= $_(elem).nextAll('a:last').text();
						$_('#GalleryDialog').find('#GalleryTitleInput').val(title);
						var id		= $_(elem).nextAll('a:last').attr('Galleryid');
						Cgallery.fn.getPic(id)
						$_("#GalleryDialog").dialog({
													title: ' ویرایش ' + title,
													buttons :	{
																	"انصراف"	: function() {$_(this).dialog('close');},
																	"ویرایش"	: function()  
																		{
																			imglength		= $_('#ImageManager').find('img').length;
																			GalleryTitle	= $_('#GalleryDialog #GalleryTitleInput').val();
																			if (GalleryTitle!='')
																			{
																				if(imglength==0)
																				{
																					$var.alert('لطفا تعدادی عکس برای نمایش در آلبوم انتخاب کنید');
																				}
																				else if(imglength<2)
																				{
																					$var.alert('تعداد عکس های انتخابی باید بیشتر از یک باشد');
																				}
																				else{
																					$_(this).dialog('close');
																					Cgallery.fn.replaceGalleryImage(id);
																																											
																				}
																			}else{
																				$var.alert('لطفا عنوان گالری را وارد کنید');	
																			}
																			
																		}
																}
													});
						$_('#GalleryDialog').dialog('open');
					}
					else if (state=='new')
					{
						$_('#GalleryDialog').find('#GalleryTitleInput').val('');
						$_('#GalleryDialog').find('#ImageManager').html('');
						$_("#GalleryDialog").dialog({ 
													title: 'ایجاد آلبوم جدید',
													buttons :	{
																	"انصراف"	: function() {$_(this).dialog('close');},
																	"تأیید"		: function()  
																		{
																			GalleryTitle	= $_('#GalleryDialog #GalleryTitleInput').val();
																			imglength		= $_('#ImageManager').find('img').length
																			if (GalleryTitle!='')
																			{
																				if(imglength==0)
																				{
																					$var.alert('لطفا تعدادی عکس برای نمایش در آلبوم انتخاب کنید');
																				}else if(imglength<=3)
																				{
																					$var.alert('تعداد عکس های انتخابی باید بیشتر از سه باشد');
																				}else{
																					$_(this).dialog('close');
																					Cgallery.fn.CreateButtonHandle();
																				}
																			}else{
																				$var.alert('لطفا عنوان گالری را وارد کنید');	
																			}
																		}
																}
													});
						$_('#GalleryDialog').dialog('open');
					}
					return this;
				},				
		CreateButtonHandle:function()
				{
					//images			= '';
					images = [];
					$_('#ImageManager').find('img').each(function(){
							//images += $_(this).attr('src')+','
							var _DATA = $_(this).data('metaData');
							if(typeof _DATA != 'object')	_DATA = {};
							_DATA['src'] = $_(this).attr('src').replace("/.thumbs/images", "/images");
							images.push( _DATA );
							});
					
					
					GalleryTitle	= $_('#GalleryDialog #GalleryTitleInput').val();
					$_.ajax(
					{
						type	: 'post', 
						dataType: 'json',	
						url		: '/admin/ajaxset/savegallery', 
						data	: {'images':images,'title':GalleryTitle},
						success	: function(data){ $var.paging.refresh('gallery',1); $var.alert(data[1]);}
					});
					return this;
				},				
		newwinOpen:function(elem)
				{
					galleryid = $_(elem).nextAll('a:last').attr('galleryid');
					GalleryWin = window.open('/gallery/'+galleryid);
					return this;
				},
		openImageManager_multipleFiles:function(elem)
				{
					window.KCFinder = {};
					window.KCFinder.callBackMultiple = function(files) {
							window.KCFinder = null;
							Cgallery.fn.insertPic(files);
						};
					window.open('/finder/browse.php?type=images',
								'kcfinder_multiple',
								'top=50, left=50, width=800, height=400, fullscreen=no, toolbar=no, status=no, menubar=no, scrollbars=yes, resizable=yes');
					return this;
				},
		insertPic:function(imgUrl)
				{
					value='';
					for (var i=0; i < imgUrl.length; i++)
					{
							
							var strthumbs = ".thumbs";
							
							if(!imgUrl[i].match(strthumbs))
							{
							imgUrl[i]= imgUrl[i].replace("/images","/.thumbs/images")
							}
							
							value += '<img  src="'+imgUrl[i]+'" />';
							
					}
					$_('#ImageManager').append(value);
					return this;
				},
		addDataToImage:function(elem)
				{
					$var.LastImageForData = elem;
					$var.alert(
					'<div id="addDataToGalleryImagesForm" style="direction: rtl; width: 330px; height: 135px;">'
					+'<p>کلید: <input type="text" style="direction:ltr;" id="ImageDataKey"></p>'
					+'<p>محتوا:<br /> <textarea style="width: 100%;" id="ImageDataValue" type="text"> </textarea></p>'
					+'</div>', 
					{
						text: "تأیید",
						click: function() { 
							var dKey = $_('#addDataToGalleryImagesForm #ImageDataKey').val();
							var dVal = $_('#addDataToGalleryImagesForm #ImageDataValue').val();
							
							
							if(/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(dKey) == false) 
							{
							    $var.alert('فیلد کلید نادرست است');
								return;	
							}							
							$_('#addDataToGalleryImagesForm #ImageDataKey').val('');
							$_('#addDataToGalleryImagesForm #ImageDataValue').val('');
							
							var _DATA = $_($var.LastImageForData).data('metaData');
							if(typeof _DATA !='object') _DATA = {};
							_DATA[dKey] = dVal;
							$_($var.LastImageForData).data('metaData', _DATA);
					
						 }
					}, 370);
				},	
		replaceGalleryImage:function(id)
				{
					//images			= '';
					//$_('#ImageManager').find('img').each(function(){images += $_(this).attr('src')+','});
					
					images = [];
					$_('#ImageManager').find('img').each(function(){
							var _DATA = $_(this).data('metaData');
							if(typeof _DATA != 'object')	_DATA = {};
							_DATA['src'] = $_(this).attr('src').replace("/.thumbs/images", "/images");
							images.push( _DATA );
							});
					
					GalleryTitle	= $_('#GalleryDialog #GalleryTitleInput').val();	
					$var.editedGallery = id;
					$_.ajax(
					{
						type	: 'post', 
						dataType: 'json',	
						url		: '/admin/ajaxset/editgallery', 
						data	: {'images':images,'title':GalleryTitle,'id':id},
						success	: function(data){$var.paging.refresh('gallery',1); Cbreedable.fn.refreshPageContent({type:'gallery', unic:$var.editedGallery}); $var.alert(data[1]);}
					});						},
		getPic:function(id)
				{
					$_.ajax({
						   url: '/admin/ajaxget/getgallerypic',
						   type: 'post',
						   dataType: 'json',
						   data:{'id':id } ,
						   success : function (_gal_data)
						   				{
											$_('#GalleryDialog').find('#ImageManager').html("");
											if(_gal_data.length==0) return;
											for(ati in _gal_data)
											{
												if(typeof _gal_data[ati]['src'] == 'undefined' ) continue;
												$_('#GalleryDialog').find('#ImageManager').append('<img  src="'+_gal_data[ati]["src"].replace("/images","/.thumbs/images")+'"  />');
												var _DATA = {};
												for(attr in _gal_data[ati])
													if(attr != 'src') _DATA[attr] = _gal_data[ati][attr];
												$_('#GalleryDialog').find('#ImageManager img').last().data('metaData', _DATA);
												
											}
										}
						   });
				}

	};