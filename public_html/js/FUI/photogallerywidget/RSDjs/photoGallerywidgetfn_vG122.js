$(function(){
photoGallerywidgetfn_vG122=function(galleryID, scz, showdelay, frstshow)
{
	var frstlftimg=0, slctdimgfrshw=0, galsz=$('#'+galleryID).find(".filmstrip").contents("div").size(); //Gallery Size
	frstshow--;
	$('#'+galleryID).find(".filmstrip").width(((scz*106)+20)+"px");
	$('#'+galleryID).find(".filmstrip").contents("div").slice(scz).css({'display':'none'});
	$('#'+galleryID).find(".filmstrip").contents("div:eq("+frstshow+")").css({"padding":"4px",  "margin-top":"2px"});
	var splitscr=$('#'+galleryID).find(".filmstrip").contents("div:eq("+frstshow+")").children('img').attr('src').split('.thumbs/');
	$('#'+galleryID).find(".stndrdszimg_holder").html('<img src="'+splitscr.join('')+'" />' );
	$('#'+galleryID).find(".filmstrip").contents("div").bind("click", function(){
		slctdimgfrshw=$(this).index();
		$('#'+galleryID).find(".filmstrip").contents("div").css({"padding":"0", "margin-top":"3px"});
		$('#'+galleryID).find(".filmstrip").contents("div:eq("+slctdimgfrshw+")").css({"padding":"4px", "margin-top":"2px"});
	var splitscr=$('#'+galleryID).find(".filmstrip").contents("div:eq("+slctdimgfrshw+")").children('img').attr('src').split('.thumbs/');
	$('#'+galleryID).find(".stndrdszimg_holder").html('<img src="'+splitscr.join('')+'" />' );
	});
	$('#'+galleryID).find("#nextbtm").bind("click", function () {
			if(galsz>scz){
			if(frstlftimg==(galsz-scz) & (galsz-scz)>=scz ){
			$('#'+galleryID).find(".filmstrip").contents("div").slice(galsz-scz).hide(showdelay);
			$('#'+galleryID).find(".filmstrip").contents("div").slice(0,scz).show(showdelay);
			frstlftimg=0; 
			 }else if(frstlftimg<(galsz-scz)){
			$('#'+galleryID).find(".filmstrip").contents("div:eq("+frstlftimg+")").hide(showdelay);
			$('#'+galleryID).find(".filmstrip").contents("div:eq("+(frstlftimg+scz)+")").show(showdelay);frstlftimg++;
			 }
			}
	 });
	$('#'+galleryID).find("#prevbtm").bind("click", function () {
				if(galsz>scz){
				if(frstlftimg==0 & (galsz-scz)>=scz){
				$('#'+galleryID).find(".filmstrip").contents("div").slice(0,scz).hide(showdelay);
				$('#'+galleryID).find(".filmstrip").contents("div").slice(galsz-scz).show(showdelay);
				frstlftimg=(galsz-scz); 
				}else if(frstlftimg>0){
				frstlftimg--;
				$('#'+galleryID).find(".filmstrip").contents("div:eq("+frstlftimg+")").show(showdelay);
				$('#'+galleryID).find(".filmstrip").contents("div:eq("+(frstlftimg+scz)+")").hide(showdelay);
				}
				}
	 });
}
Destroy_photoGallerywidgetfn_vG122=function(galleryID)
{
	$('#'+galleryID).find(".filmstrip").contents("div").unbind("click");
	$('#'+galleryID).find("#nextbtm").unbind("click");
	$('#'+galleryID).find("#prevbtm").unbind("click");
}
});
