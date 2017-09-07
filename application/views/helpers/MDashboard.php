<?php

class App_View_Helper_MDashboard extends Zend_View_Helper_Abstract
{
    
    public function mDashboard($mdi)
    {
		if(!is_numeric($mdi))	return $this->clearMDash($mdi);
		$this->DB	= Zend_registry::get('front_db');
		if(! $dmi	= $this->getMDash($mdi))	return $this->clearMDash($mdi);
		$this->u_condition	= $this->setUserCondition();
		if(! $dm	= $this->getDashMenu($dmi))	return $this->clearMDash($mdi);
		$this->setSession($mdi);
		return	$this->skinDashMenu($dm);
    }
    public function clearMDash($mdi)
    {
		if(isset($_SESSION['MyApp']['mdi']))	unset($_SESSION['MyApp']['mdi']);
		return '';
    }
    public function setSession($mdi)
    {
		$_SESSION['MyApp']['mdi'] = $mdi;
    }
    public function getMDash($mdi)
    {
		$sql	= 'SELECT `menu_id` FROM `wbs_manual_dashboard` WHERE `wbs_id`='.WBSiD.' AND `id`='.$mdi;
		$result	= $this->DB->fetchOne($sql);
		if(empty($result)) return false;
		return $result;
    }
    public function getDashMenu($dmi)
    {
		$sql	=  "SELECT `content` FROM wbs_menu WHERE wbs_id='".WBSiD."' AND id=".$dmi .$this->u_condition;
		$result	= $this->DB->fetchAll($sql);
		if(empty($result)) return false;
		return $result[0]['content'];
    }
    public function skinDashMenu($content)
    {
		$content= '<root>'.trim($content).'</root>';
		if(strlen($content)<2) return '';
		$data	= array(
					'xml'	=> $content,
					'db'	=> $this->DB,
					'temp'	=> $this->getMDSkin(),
					'page'	=> ''
					);
		$menu	= Application_Model_Helper_Page::parseMlMenu($data);
		$style	= '<style type="text/css">body{margin-top:27px;} #manualDashboard .rdm-menu a, #manualDashboard .rdm-menu a:link, #manualDashboard .rdm-menu a:visited, #manualDashboard .rdm-menu a:hover { text-align: right; text-decoration: none; outline: none; letter-spacing: normal; word-spacing: normal;font-size:11px;} #manualDashboard .rdm-menu, #manualDashboard .rdm-menu ul { margin: 0; padding: 0; border: 0; list-style-type: none; display: block; } #manualDashboard .rdm-menu li { margin: 0; padding: 0; border: 0; display: block; float: right; position: relative; z-index: 5; background: none; } #manualDashboard .rdm-menu li:hover { z-index: 10000; white-space: normal; } #manualDashboard .rdm-menu li li { float: none; } #manualDashboard .rdm-menu ul { visibility: hidden; position: absolute; z-index: 10; right: 0; top: 0; background: none; } #manualDashboard .rdm-menu li:hover>ul { visibility: visible; top: 100%; } #manualDashboard .rdm-menu li li:hover>ul { top: 0; right: 100%; } #manualDashboard .rdm-menu:after, #manualDashboard .rdm-menu ul:after { content: "."; height: 0; display: block; visibility: hidden; overflow: hidden; clear: both; } #manualDashboard .rdm-menu, #manualDashboard .rdm-menu ul { min-height: 0; } #manualDashboard .rdm-menu ul { background-image: url(/modules/dashboard/md/images/spacer.gif); padding: 10px 30px 30px 30px; margin: -10px -30px 0 0; } #manualDashboard .rdm-menu ul ul { padding: 30px 10px 30px 30px; margin: -30px -10px 0 0 ; } #manualDashboard .rdm-menu { padding: 0 0 0 0; } #manualDashboard { width:100%; position: absolute; top:0; left:0; height: 27px; z-index: 100; } #manualDashboard .l, #manualDashboard .r { position: absolute; z-index: -1; top: 0; height: 27px; background-image: url(\'/modules/dashboard/md/images/nav.png\'); } #manualDashboard .l { left: 0; right: 0; } #manualDashboard .r { right: 0; width: 888px; clip: rect(auto, auto, auto, 888px); } #manualDashboard .rdm-menu a { position: relative; display: block; overflow: hidden; height: 27px; cursor: pointer; text-decoration: none; } #manualDashboard .rdm-menu li { margin-right: 0; margin-left: 0; } #manualDashboard .rdm-menu ul li { margin:0; clear: both; } #manualDashboard .rdm-menu a .r, #manualDashboard .rdm-menu a .l { position: absolute; display: block; top: 0; z-index: -1; height: 81px; background-image: url(\'/modules/dashboard/md/images/menuitem.png\'); } #manualDashboard .rdm-menu a .l { left: 0; right: 0; } #manualDashboard .rdm-menu a .r { width: 400px; right: 0; clip: rect(auto, auto, auto, 400px); } #manualDashboard .rdm-menu a .t { margin-right: 10px; margin-left: 10px; font-family: Tahoma, Arial, Helvetica, Sans-Serif; color: #FFFFFF; padding: 0 8px; margin: 0 0; line-height: 27px; text-align: center; } #manualDashboard .rdm-menu a:hover .l, #manualDashboard .rdm-menu a:hover .r { top: -27px; } #manualDashboard .rdm-menu li:hover>a .l, #manualDashboard .rdm-menu li:hover>a .r { top: -27px; } #manualDashboard .rdm-menu li:hover a .l, #manualDashboard .rdm-menu li:hover a .r { top: -27px; } #manualDashboard .rdm-menu a:hover .t { color: #F0F3F5; } #manualDashboard .rdm-menu li:hover a .t { color: #F0F3F5; } #manualDashboard .rdm-menu li:hover>a .t { color: #F0F3F5; } #manualDashboard .rdm-menu-separator { display: block; width: 1px; height: 27px; background-image: url(\'/modules/dashboard/md/images/menuseparator.png\'); } #manualDashboard .rdm-menu ul a { display: block; text-align: center; white-space: nowrap; height: 20px; width: 180px; overflow: hidden; line-height: 20px; background-image: url(\'/modules/dashboard/md/images/subitem.png\'); background-position: left top; background-repeat: repeat-x; border-width: 0px; border-bottom-width:1px; border-style: solid; border-color: #D7E3EA; } #manualDashboard ul.rdm-menu ul span, #manualDashboard ul.rdm-menu ul span span { display: inline; float: none; margin: inherit; padding: inherit; background-image: none; text-align: inherit; text-decoration: inherit; } #manualDashboard .rdm-menu ul a, #manualDashboard .rdm-menu ul a:link, #manualDashboard .rdm-menu ul a:visited, #manualDashboard .rdm-menu ul a:hover, #manualDashboard .rdm-menu ul a:active, #manualDashboard ul.rdm-menu ul span, #manualDashboard ul.rdm-menu ul span span { text-align: right; text-indent: 12px; text-decoration: none; line-height: 20px; color: #FFFFFF; margin-right: 10px; margin-left: 10px; font-family: Tahoma, Arial, Helvetica, Sans-Serif; margin:0; padding:0; } #manualDashboard .rdm-menu ul li a:hover { color: #000000; border-color: #95B3C6; background-position: 0 -20px; } #manualDashboard .rdm-menu ul li:hover>a { color: #000000; border-color: #95B3C6; background-position: 0 -20px; } #manualDashboard .rdm-menu ul li a:hover span, #manualDashboard .rdm-menu ul li a:hover span span { color: #000000; } #manualDashboard .rdm-menu ul li:hover>a span, #manualDashboard .rdm-menu ul li:hover>a span span { color: #000000; } </style><!--[if IE 6]><style type="text/css"> #manualDashboard .rdm-menu ul { width: 1px; } #manualDashboard .rdm-menu li.rdm-menuhover { z-index: 10000; } #manualDashboard .rdm-menu .rdm-menuhoverUL { visibility: visible; } #manualDashboard .rdm-menu .rdm-menuhoverUL { top: 100%; right: 0; } #manualDashboard .rdm-menu .rdm-menuhoverUL .rdm-menuhoverUL { top: 0; right: 100%; } #manualDashboard .rdm-menu .rdm-menuhoverUL .rdm-menuhoverUL { top: 5px; right: 100%; } #manualDashboard .rdm-menu, #manualDashboard .rdm-menu ul, #manualDashboard .rdm-menu ul a { height: 1%; } #manualDashboard .rdm-menu li.rdm-menuhover { z-index: 10000; } #manualDashboard .rdm-menu .rdm-menuhoverUL { visibility: visible; } #manualDashboard .rdm-menu .rdm-menuhoverUL { top: 100%; right: 0; } #manualDashboard .rdm-menu .rdm-menuhoverUL .rdm-menuhoverUL { top: 0; right: 100%; } #manualDashboard .rdm-menu li li { float: right; width: 100%; } #manualDashboard { zoom: 1; } #manualDashboard .l, #manualDashboard .r { font-size: 1px; background: none; behavior: expression(this.runtimeStyle.filter?\'\':this.runtimeStyle.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'/modules/dashboard/md/images/nav.png\',sizingMethod=\'crop\')"); } #manualDashboard .l { width: expression(this.parentNode.offsetWidth-0+\'px\'); } #manualDashboard .r { left: expression(this.parentNode.offsetWidth-888+\'px\'); clip: rect(auto auto auto 888px); } #manualDashboard .rdm-menu a { float: right; } #manualDashboard .rdm-menu a:hover { visibility: visible; } #manualDashboard .rdm-menu a .r, #manualDashboard .rdm-menu a .l { font-size: 1px; background: none; behavior: expression(this.runtimeStyle.filter?\'\':this.runtimeStyle.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'/modules/dashboard/md/images/menuitem.png\',sizingMethod=\'crop\')"); } #manualDashboard .rdm-menu a .r { left: expression(this.parentNode.offsetWidth-400+\'px\'); clip: rect(auto auto auto 400px); } #manualDashboard .rdm-menu a .l { width: expression(this.parentNode.offsetWidth-0+\'px\'); } .rdm-menuhover .rdm-menuhoverA .t { color: #F0F3F5; } .rdm-menuhover .rdm-menuhoverA .l, .rdm-menuhover .rdm-menuhoverA .r { top: -27px; } #manualDashboard .rdm-menu-separator { font-size: 1px; zoom: 1; background: none; behavior: expression(this.runtimeStyle.filter?\'\':this.runtimeStyle.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'/modules/dashboard/md/images/menuseparator.png\',sizingMethod=\'crop\')"); } #manualDashboard .rdm-menu ul a { color: #FFFFFF !important; } #manualDashboard .rdm-menu ul a:hover { color: #000000 !important; } #manualDashboard .rdm-menu ul .rdm-menuhover .rdm-menuhoverA { color: #000000 !important; border-color: #95B3C6; background-position: 0 -20px; } #manualDashboard .rdm-menu ul a:hover span, #manualDashboard .rdm-menu ul a:hover span span { color: #000000 !important; } #manualDashboard .rdm-menu ul .rdm-menuhover .rdm-menuhoverA span, #manualDashboard .rdm-menu ul .rdm-menuhover .rdm-menuhoverA span span { color: #000000 !important; } </style><![endif]--><!--[if IE 7]><style type="text/css"> #manualDashboard .r { clip: rect(auto auto auto 888px); } #manualDashboard .rdm-menu a .r { clip: rect(auto auto auto 400px); } </style><![endif]-->
<script type="text/javascript"> function rdmGetElementsByClassName(clsName, parentEle, tagName) { var elements = null;var found = [];	var s = String.fromCharCode(92);var re = new RegExp(\'(?:^|\' + s + \'s+)\' + clsName + \'(?:$|\' + s + \'s+)\');if (!parentEle) parentEle = document;	if (!tagName) tagName = \'*\';	elements = parentEle.getElementsByTagName(tagName);	if (elements) {	for (var i = 0; i < elements.length; ++i) {	if (elements[i].className.search(re) != -1) {found[found.length] = elements[i];}}}	return found;} function rdmGTranslateFix() {var menus = rdmGetElementsByClassName("rdm-menu", document, "ul");for (var i = 0; i < menus.length; i++) {var menu = menus[i];var childs = menu.childNodes;var listItems = [];for (var j = 0; j < childs.length; j++) {var el = childs[j];if (String(el.tagName).toLowerCase() == "li") listItems.push(el); } for (var j = 0; j < listItems.length; j++) { var item = listItems[j]; var a = null; var gspan = null; for (var p = 0; p < item.childNodes.length; p++) { var l = item.childNodes[p]; if (!(l && l.tagName)) continue; if (String(l.tagName).toLowerCase() == "a") a = l; if (String(l.tagName).toLowerCase() == "span") gspan = l; } if (gspan && a) { var t = null; for (var k = 0; k < gspan.childNodes.length; k++) { var e = gspan.childNodes[k]; if (!(e && e.tagName)) continue; if (String(e.tagName).toLowerCase() == "a" && e.firstChild) e = e.firstChild; if (e && e.className && e.className == \'t\') { t = e; if (t.firstChild && t.firstChild.tagName && String(t.firstChild.tagName).toLowerCase() == "a") { while (t.firstChild.firstChild) t.appendChild(t.firstChild.firstChild); t.removeChild(t.firstChild); } a.appendChild(t); break; } } gspan.parentNode.removeChild(gspan); } } } } function rdmAddMenuSeparators() { var menus = rdmGetElementsByClassName("rdm-menu", document, "ul"); for (var i = 0; i < menus.length; i++) { var menu = menus[i]; var childs = menu.childNodes; var listItems = []; for (var j = 0; j < childs.length; j++) { var el = childs[j]; if (String(el.tagName).toLowerCase() == "li") listItems.push(el); } for (var j = 0; j < listItems.length - 1; j++) { var item = listItems[j]; var span = document.createElement(\'span\'); span.className = \'rdm-menu-separator\'; var li = document.createElement(\'li\'); li.appendChild(span); item.parentNode.insertBefore(li, item.nextSibling); } } } function rdmMenuIE6Setup() { var isIE6 = navigator.userAgent.toLowerCase().indexOf("msie") != -1 && navigator.userAgent.toLowerCase().indexOf("msie 7") == -1; if (!isIE6) return; var aTmp2, i, j, oLI, aUL, aA; var aTmp = rdmGetElementsByClassName("rdm-menu", document, "ul"); for (i = 0; i < aTmp.length; i++) { aTmp2 = aTmp[i].getElementsByTagName("li"); for (j = 0; j < aTmp2.length; j++) { oLI = aTmp2[j]; aUL = oLI.getElementsByTagName("ul"); if (aUL && aUL.length) { oLI.UL = aUL[0]; aA = oLI.getElementsByTagName("a"); if (aA && aA.length) oLI.A = aA[0]; oLI.onmouseenter = function() { this.className += " rdm-menuhover"; this.UL.className += " rdm-menuhoverUL"; if (this.A) this.A.className += " rdm-menuhoverA"; }; oLI.onmouseleave = function() { this.className = this.className.replace(/rdm-menuhover/, ""); this.UL.className = this.UL.className.replace(/rdm-menuhoverUL/, ""); if (this.A) this.A.className = this.A.className.replace(/rdm-menuhoverA/, ""); }; } } } } rdmGTranslateFix();rdmAddMenuSeparators();rdmMenuIE6Setup(); </script>';
		$menu	= '<div id="manualDashboard"><div class="l"></div><div class="r"></div><ul class="rdm-menu">'.implode('',$menu).'</ul>'.$style.'</div>';
		return 	$menu;
    }
	public function getMDSkin() 
	{
	
		$skin[0]	= '<li><a href="#rasta-linkhref#"><span class="l"></span><span class="r"></span><span class="t">#rasta-linktitle#</span></a>#rasta-submenu#</li>';
		$skin[1]	= '<ul>#rasta-submenuContent#</ul>';
		$skin[2]	= '<li><a href="#rasta-linkhref#">#rasta-linktitle#</a>#rasta-submenu#</li>';
		$skin[3]	= '<ul>#rasta-submenuContent#</ul>';
		$skin[4]	= '<li><a href="#rasta-linkhref#">#rasta-linktitle#</a></li>';
		return $skin;
    }
	public function setUserCondition() 
	{
		$ses = new Zend_Session_Namespace('Zend_Auth');
		$u_condition	= '';
		if($ses->storage->is_admin!=1)
		{
			if(!empty($ses->storage->user_group) )
				$u_condition	= " OR `user_group` LIKE '%/".preg_replace('/\//', "/%' OR `user_group` LIKE '%/", $ses->storage->user_group)."/%'";
			$u_condition	= " AND (`user_group`='0'".$u_condition.")";
		}
		return $u_condition;
	}
    
}