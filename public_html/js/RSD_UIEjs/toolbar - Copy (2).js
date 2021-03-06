function Ctoolbar()
{
	return Ctoolbar.fn.init();
}
Ctoolbar.fn=Ctoolbar.prototype=
{
	init:function()
			{
				$_('#toolbar .toolbutt')
					.each(function()
					{
						$_(this).button({
										text: false,
										icons:{
												primary: 'ui-icon-'+$_(this).attr('icon')
											  }
										})
										.click(function()
										{
											Ctoolbar.fn[$_(this).attr('act')]();
										});
					})
				return this;
			},
	savePage:function()
		{		
			
			
			SectionCloneHTML='';
			var headerMenuDisplay = $_('.headerMenu').css('display');
			if(headerMenuDisplay!='none')
			{
				headerClone	= $_('.headerMenu').clone();
				headerClone.filter(function(){SectionCloneHTML +='<div class="headermenu" unic="'+$_(this).attr('unic')+'" ></div>'; })				
			}
			containerClone=$_('#container').find('.rasta-section').clone();
			containerClone.contents().text('').attr('class', '');
			containerClone.find('#contentplaceholder').remove();
			containerClone.find('[unic=""]').remove();
			containerClone.find(':not([type])').remove();
			
			containerClone.each(function()
					{
						var targetSection = $_(this).attr('class').match(/section\-\d/)[0].replace('section-', '');
						SectionCloneHTML +='<div section="'+targetSection+'" >'+$_(this).html().toLowerCase()+'</div>';
					});
			var slogan		= $_('textarea[name=slogan]').val();
			var authors		= $_('textarea[name=authors]').val();
			var description	= $_('textarea[name=description]').val();
			var keywords	= $_('textarea[name=keywords]').val();
			var skin		= $_('input[name=skin]').val();
			var menuskin	= $_('input[name=mlmskin]').val();
			var pagedir		= $_('input[name=page_dir]').val();
			$_.ajax({
				url:'/admin/ajaxset/savepagecontent',
				type		: 'post',
				dataType	: 'json',
				data:{'content':SectionCloneHTML ,'pageID': $var.config.site.pageId , 'slogan':slogan ,'authors':authors ,'description':description ,'keywords':keywords ,'skin':skin, 'menuskin':menuskin, 'pagedir':pagedir},
				success:this.saveSuccess
			})
		},
	saveSuccess:function(msg)
		{
			$var.alert(msg[1]);
		},
	prevPage:function()
		{
			opendwindow	=	window.open('/page/'+$var.config.site.pageId);
		},
	rtcManager:function()
		{
			Crtc.fn.rtcPopupAction=false;
			popupwin= window.open($var.config.baseURL.rtcm+'/frmlistcnt/index/env/dsh#fragment-2','RTCMANAGER');
			return this;
		},
	fileManager:function()
		{
			if (typeof fileManagerWin =='undefined') fileManagerWin={closed:true};
			if(fileManagerWin.closed==false)
			{
				fileManagerWin.focus();
			}
			else
			{
				fileManagerWin = null;
				fileManagerWin	=	window.open(
											'/finder/browse.php?type=files&action=manager&lang='+$var.config.site.lang,
											'FileManager',
											"location=1,status=1,scrollbars=1,resizable=1,width=900,height=500"
											);
				fileManagerWin.moveTo(100,50);
			}
			return this;
		},
	siteSetting:function()
	{
		window.open('/admin/index/sitesetting/env/dsh#fragment-1');
	},
	logout:function()
	{
		window.location = '/admin/user/logout';
	}
		
		
}
