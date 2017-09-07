function Csearchable()
{
	return Csearchable.fn.init();
}
Csearchable.fn=Csearchable.prototype=
{
	allSearchable: {
		rtcsSearch:["rtclist", "#LogRtcsSearch", Crtc.fn.setRtcList],
		gallerySearch:["Gallerylist", "#LogGallerySearch", Cgallery.fn.setGalleryList],
		menuSearch:["menulist", "#LogMenuSearch", Cmenu.fn.setMenuList],
		pageSearch:["pagelist", "#LogPageSearch", Cpage.fn.setPageList],
		scenarioSearch:["scenariolist", "#LogScenarioSearch", Cscenario.fn.setScenarioList],
		extLinkSearch:["extlinklist","#LogExtLinkSearch", CextLink.fn.setLinkList]
		},
	init:function()
			{
				for(prop in this.allSearchable)
				{
					$_('#'+prop)
						.autocomplete({
							source: "/admin/ajaxget/autocomplete/t/"+this.allSearchable[prop][0]+"/", 
							minLength: 2,
							select: function( event, ui ) {
								$_($var.searchable.allSearchable[this.id][1])
										.prepend('<div class="rasta-buttonset">'+ ui.item.data +'</div>');
								$var.searchable.allSearchable[this.id][2]();
								$var.breedable.setBreedables();
								
							},
							close: function(event, ui) 
							{
								$_(this).val('')
							}

						});
				}
				return this;
			}
};

$_(function(){
$var.searchable= new Csearchable();
});
