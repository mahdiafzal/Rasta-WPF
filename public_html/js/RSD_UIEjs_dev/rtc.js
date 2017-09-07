/* RTC */
	function Crtc()
	{
		return Crtc.fn.init();
	}
	
	Crtc.fn=Crtc.prototype=
	{
		rtcPopupAction:true,
		init:function()
				{
					this.getRtcs().setListTools();
					return this;
				},
		setListTools:function()
				{
					setListTools('#rtclistpanel .listtools', this.New )
					return this;
				},
		New:function()
				{
					this.rtcPopupAction = true;
					$var.editedRtc = null;
					if (typeof rtcPopupWin =='undefined') rtcPopupWin={closed:true};
					if(!rtcPopupWin.closed)
					{
						rtcPopupWin.focus();
					}
					else
					{					
						rtcPopupWin = window.open(config.baseURL.rtcm+'/frmregister',
									'NewRTC',
									"location=1,status=1,scrollbars=1,resizable=1,width=1003,height=500"
									);
						rtcPopupWin.moveTo(150,50);
					}
					return this;
				},
		newBind:function()
				{
					$_('#addnewrtc').click(function()
									{
										Crtc.fn.New();
									});
					return this;
				},
		getRtcs:function()
				{
										Crtc.fn.setRtcList();
					return this;
				},
		setRtcList:function()
				{
					$_('#RTClist .RTCtitle')
						.each(function()
						{
							Crtc.fn.setRtcButtonSet(this);
						});
					return this;
				},
		setRtcButtonSet:function(elem)
				{
							$_(elem)
								.button()
								.click(function() 
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
												Crtc.fn.newwinOpen(this);
								})
							.prev('a')
								.button( {
									text: false,
									icons: {
										primary: "ui-icon-trash"
									}
								})
								.click(function() {
									Crtc.fn.deleteButtonHandle(this);	
								})
							.prev('a')
								.button( {
									text: false,
									icons: {
										primary: "ui-icon-wrench"
									}
								})
								.click(function() {
									Crtc.fn.editButtonHandle(this);
								})
							.parent()
								.buttonset();
					return this;
				},
		editButtonHandle:function(elem)
				{
					rtcid = $_(elem).nextAll('a:last').attr('rtcid');
					if(typeof elem =='string') rtcid = elem;
					this.rtcPopupAction=true;
					$var.editedRtc = rtcid;

					if (typeof rtcEditWindow =='undefined') rtcEditWindow={closed:true};
					if(!rtcEditWindow.closed)
					{
						if(rtcEditWindow.name!='edit '+rtcid)
						{
							rtcEditWindow.location	= config.baseURL.rtcm+'/frmregister/index/id/'+rtcid;
							rtcEditWindow.name		= 'edit '+rtcid;
							rtcEditWindow.focus();
							rtcEditWindow.moveTo(150,50);							
						}
						else
						{
							rtcEditWindow.focus();
						}
					}
					else
					{
						rtcEditWindow	=	window.open(config.baseURL.rtcm+'/frmregister/index/id/'+ rtcid ,
														'edit'+rtcid,
														"location=1,status=1,scrollbars=1,resizable=1,width=1003,height=500"
														);
						rtcEditWindow.moveTo(150,50);
					}
					return this;
				},
		deleteButtonHandle:function(elem)
				{
					rtcid = $_(elem).nextAll('a:last').attr('rtcid');
					this.rtcPopupAction = true;
					
					if (typeof rtcDelWindow =='undefined') rtcDelWindow={closed:true};
					if(!rtcDelWindow.closed)
					{
						if(rtcDelWindow.name!='delete '+rtcid)
						{
							rtcDelWindow.location	= config.baseURL.rtcm+'/frmdelcnt/index/id/'+rtcid;
							rtcDelWindow.name		= 'delete '+rtcid;
							rtcDelWindow.focus();
							rtcDelWindow.moveTo(150,50);							
						}
						else
						{
							rtcDelWindow.focus();
						}
					}
					else
					{
						rtcDelWindow = window.open(	config.baseURL.rtcm+'/frmdelcnt/index/id/'+rtcid,
													'delete'+rtcid,
													"location=1,status=1,scrollbars=1,resizable=1,width=1003,height=400"
													);
						rtcDelWindow.moveTo(150,50);
					}
					return this;
				},				
		newwinOpen:function(elem)
				{
					rtcid = $_(elem).nextAll('a:last').attr('rtcid');
					RTCWin = window.open('/rtc/'+rtcid);
					return this;
				}
	};
