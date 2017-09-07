function Cscenario()
{
	return Cscenario.fn.init();
}
Cscenario.fn=Cscenario.prototype=
{
	buffer: {},
	scnarioOptions:[ lang.am, lang.an],
	init:function()
			{
				this.setScenarioList().setListTools().setDialog().autocomplete().bindScenarioSelect();
				$var.SelectedAction	= 1;
				return this;
			},
	New:function()
			{
				Cscenario.fn.openDialog(this,'new');
			},
	newBind:function()
			{
				$_('#scenariolistpanel .listtools .ui-button:eq(0)')
					.click(function()
								{
									Cscenario.fn.New();
								});
				return this;
			},
	setScenarioList:function()
			{
				$_('#scenariolist .scenariotitle')
					.each(function()
					{
						Cscenario.fn.setScenarioButtonSet(this);
					});
				return this;
			},
	setListTools:function() 
			{
				setListTools('#scenariolistpanel .listtools', this.New )
				return this;
			},
	autocomplete:function()
	{
		$_('#scen_page')
			.autocomplete({
				source: "/admin/ajaxget/autocomplete/t/pageid/", 
				minLength: 1,
				appendTo: '#paneldialogAuto',
				select: function( event, ui ) {
						$_(this).next('span').text(ui.item.data);				
				}
			});
		return this;
	},
	setScenarioButtonSet:function(elem)
			{
						$_(elem)
							.button()
							.dblclick(function() 
							{
								$_(this).prev('a').click();
							})
						.prev('a')
							.button( {
								text: false,
								icons: {
									primary: "ui-icon-newwin"
								}
							})
							.click(function() {
											Cscenario.fn.newwinOpenscenario(this);
							})
						.prev('a')
							.button( {
								text: false,
								icons: {
									primary: "ui-icon-wrench"
								}
							})
							.click(function() {
											Cscenario.fn.openDialog(this,'edit');
							})
						.parent()
							.buttonset();
				return this;
			},
	newwinOpenscenario:function(elem)
			{
					var Thisscenario = $_(elem).nextAll('a:last');
					mywindow = window.open(Thisscenario.attr('url'), 'scenario'+Thisscenario.attr('scenarioid'));
			},
	bindScenarioSelect:function()
			{
					$_('#ScenarioSelect').live('change', function(){ $_('#scenariooptions').html(Cscenario.fn.scnarioOptions[$_(this).val()-1]);$var.SelectedAction=$_(this).val();});
					return this;
			},
	setDialog : function() 
		{
			var html	= lang.ao + this.scnarioOptions[0] + lang.aq;
			$_('<div dir="rtl" id="paneldialog"></div>')
									.dialog({ 
												width: 400,
												height: 320,
												autoOpen: false,
												resizable: false,
												buttons :[
															{text:lang.o, click:function() {$_(this).dialog('close');} },
															{text:lang.p, click:function(){
																					$var.scenvalues	= [];
																					$_(this).find('input').each(function(){ $var.scenvalues.push($_(this).val());});
																					$var.scenvalues[5] = 0;
																					if($_('#scen_paging').is(':checked')) $var.scenvalues[5] = 1;
																					if(! /^\d+$/.test($var.scenvalues[3])) 
																							$var.scenvalues[3] = $_('#scen_page').next('span').text();
										var validation = [
														  [/^.+$/, lang.ar], //'فیلد عنوان نباید خالی باشد'],
														  [/^[\w\d\s\:\-\_]+$/, lang.as], //'فیلد عنوان لاتین نباید خالی و تنها باید از حروف لاتین، عدد، فاصله و علامت های (: - _) باشد '],
														  [/^[\w\d]+[\w\d\/]*[\w\d]+$/, lang.at], //'فیلد آدرس نباید خالی و تنها باید از حروف لاتین، عدد و علامت (/) باشد '],
														  [/^\d+$/, lang.au], //'فیلد صفحه نباید خالی و تنها باید عدد باشد یا توسط جستجوی عنوان صفحه پر شود'],
														  [/^\d+$/, lang.av], //'فیلد تعداد پست نباید خالی و تنها باید عدد باشد'],
														  [/^.*$/, lang.aw] //'چک باکس']
														  ];
																					var message	= '';
																					for(i in $var.scenvalues ) 
																					{
																						if(! validation[i][0].test($var.scenvalues[i])) message +=  validation[i][1]+'<br />';
																					}
																					if(message.length>3)
																					{
																						$var.alert(message)
																						return false;
																					}
																					Cscenario.fn.saveScenario($var.scenvalues);
																					$_(this).dialog('close');
																				} }
														]
												})
									.html(html);
				return this;
			},
	openDialog : function(elem,state) 
		{
			this.buffer.elem	= elem;
			this.buffer.state	= state;
			if (state=='edit')
			{
				this.buffer.state	= $_(elem).nextAll('a:last').attr('scenarioid');
				this.getSenario(this.buffer.state);
				$_("#paneldialog").dialog({ title: lang.ay });
			}
			else if (state=='new')
			{
				$_('#paneldialog').find('input').val('').last().attr('checked',false);
				$_('#paneldialog').find('#scen_page').next('span').text('');
				$_('#ScenarioSelect').val('1'); $_('#scenariooptions').html(Cscenario.fn.scnarioOptions[0]);$var.SelectedAction=1;
				$_("#paneldialog").dialog({ title: lang.az });
				$_('#paneldialog').dialog('open');
			}
			return this;
		},
	fillForm:function(data, afterFill) 
		{
			$_('#ScenarioSelect').val(data[6]); $_('#scenariooptions').html(Cscenario.fn.scnarioOptions[data[6]-1]);$var.SelectedAction=data[6];
			$_('#paneldialog input').each(function(index){$_(this).val(data[index]);});
			$_('#paneldialog').find('#scen_page').next('span').text('');
			if(data[5]!='0') $_('#scen_paging').attr('checked',true); else $_('#scen_paging').attr('checked',false);
			if(typeof afterFill == 'function') afterFill();
		},
	getSenario:function(data) 
		{
					$_.ajax
					({
						url			: '/admin/ajaxget/getscenariodata',
						type		: 'post',
						dataType	: 'json',
						data		: {'state':data},
						success		: function(data){ if(data[0]==false){$var.alert(lang.ba);return false;} Cscenario.fn.fillForm(data[1], function(){$_('#paneldialog').dialog('open')}) }
					});
		},
	
	saveScenario:function(data)
			{
				var a_url	= '/admin/ajaxset/editscenario';
				if(this.buffer.state=='new') a_url	= '/admin/ajaxset/savescenario';
				$_.ajax
					({
						url			: a_url,
						type		: 'post',
						dataType	: 'json',
						data		: {'state':this.buffer.state,'sceaction':$var.SelectedAction, 'title':data[0] , 'latin':data[1] , 'url':data[2] , 'page':data[3] , 'count':data[4] , 'paging':data[5]},
						success		: function(msg){ $var.paging.refresh('scenario',1); $var.alert(msg[1]);} 
					
					});
			}
};