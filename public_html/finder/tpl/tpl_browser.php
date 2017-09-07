<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>KCFinder: /<?php echo $this->session['dir'] ?></title>
<?php INCLUDE "tpl/tpl_css.php" ?>
<?php INCLUDE "tpl/tpl_javascript.php" ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<script type="text/javascript">
$('body').noContext();
function resizeImage()
{
	imagenames	= '';
	$.each(	$('div#files').find('.selected .name'),
			function()
			{
				imagenames	+=$(this).text()+'*,*';
			}
		);

	imagenames		= imagenames.substring(0,imagenames.length-3);
	imagedimension	= $('input[name=Dimension]:checked').val();
	imagenewsize	= $('input[name=Newsize]:checked').val();
	
	$.ajax({ 
		type	: "POST",
		url		: "resize.php",
		data	: "name="+imagenames+"&imaged="+imagedimension+"&imagen="+imagenewsize,
		success	: function(data)
				{
					alert(data);
      			}
		  });	
}
function resizeButton()
{

	if ($('#resizebox').css('display') == 'none') {
		$('#resizebtm').addClass('selected');
		$('#resizebox').css('display', 'block');
	}else{
		$('#resizebtm').removeClass('selected');
		$('#resizebox').css('display', 'none');
	}
}
</script>
<div id="resizer"></div>
<div id="shadow"></div>
<div id="dialog"></div>
<div id="clipboard"></div>
<div id="all">
<div id="left">
    <div id="folders"></div>
</div>
<div id="right">
    <div id="toolbar">
        <div>
        <a href="kcact:upload"><?php echo $this->label("Upload") ?></a>
        <a href="kcact:refresh"><?php echo $this->label("Refresh") ?></a>
        <a href="kcact:settings"><?php echo $this->label("Settings") ?></a>
        <a href="kcact:maximize"><?php echo $this->label("Maximize") ?></a>
        <a id="resizebtm" href="javascript:resizeButton()"><?php echo $this->label("Resize") ?></a>
		<?php
		
		if(isset($_GET['action']) && $_GET['action']=='manager')
		{
			$langquery	= (!empty($_GET['lang']))?'&lang='.$_GET['lang']:'';
			$filelink	= '<a id="btnfile" href="./browse.php?type=files&action=manager'.$langquery.'">'.$this->label("Files").'</a>';
			$imagelink	= '<a id="btnimage" href="./browse.php?type=images&action=manager'.$langquery.'">'.$this->label("Images").'</a>';
			$flashlink	= '<a id="btnflash" href="./browse.php?type=flash&action=manager'.$langquery.'">'.$this->label("Flash").'</a>';
			
//			switch ($_GET['type'])
//			{
//					case 'file'  : $filelink	= '';break;
//					case 'images': $imagelink	= '';break;
//					case 'flash' : $flashlink	= '';break;
//					default		 : $filelink	= '';break;
//			}
			echo $filelink . $imagelink . $flashlink;					 
		}
		
		?>
        <div id="loading"></div>
        </div>
    </div>
	
	<div id="resizebox" style="display:none;">
	<div style="float:left;width:145px;">
	<fieldset>
		<legend><?php echo $this->label("Dimension:") ?></legend>
		<table><tr>
		<th><input type="radio" name="Dimension" value="width" checked="checked" /></th>
		<td>&nbsp;<?php echo $this->label("Width") ?> &nbsp;</td>
		<th><input type="radio" name="Dimension" value="height" /></th>
		<td>&nbsp;<?php echo $this->label("Height") ?> &nbsp;</td>
		<th><input type="radio" name="Dimension" value="both" /></th>
		<td>&nbsp;<?php echo $this->label("Both") ?></td>
		</tr></table>
	</fieldset>	
	</div>
	
	<div style="float:left; margin-left:12px;">
	<fieldset>
    <legend><?php echo $this->label("New size") ?>:</legend>
        <table id="imagess"><tr>
        <th><input id="120" type="radio" name="Newsize" value="120" checked="checked" /></th>
        <td><label for="120">&nbsp;120</label> &nbsp;</td>
        <th><input id="160" type="radio" name="Newsize" value="160" /></th>
        <td><label for="160">&nbsp;160</label> &nbsp;</td>
        <th><input id="240" type="radio" name="Newsize" value="240" /></th>
        <td><label for="240">&nbsp;240</label> &nbsp;</td>
        <th><input id="320" type="radio" name="Newsize" value="320" /></th>
        <td><label for="320">&nbsp;320</label> &nbsp;</td>
        <th><input id="360" type="radio" name="Newsize" value="360" /></th>
        <td><label for="360">&nbsp;360</label> &nbsp;</td>
        <th><input id="480" type="radio" name="Newsize" value="480" /></th>
        <td><label for="480">&nbsp;480</label> &nbsp;</td>
        <th><input id="600" type="radio" name="Newsize" value="600" /></th>
        <td><label for="600">&nbsp;600</label> &nbsp;</td>
        <th><input id="768" type="radio" name="Newsize" value="768" /></th>
        <td><label for="768">&nbsp;768</label> &nbsp;</td>
        <th><input id="800" type="radio" name="Newsize" value="800" /></th>
        <td><label for="800">&nbsp;800</label> &nbsp;</td>
        <th><input id="1024" type="radio" name="Newsize" value="1024" /></th>
        <td><label for="1024">&nbsp;1024</label> &nbsp;</td>
        </tr></table>
    </fieldset>
	</div>

	<div style="padding-top:6px;">
	<div id="toolbar">	   
			<a id="Resizebtn" href="javascript:resizeImage()">Resize</a>		
	</div>
	</div> 
		
    </div>
	
    <div id="settings">

    <div>
    <fieldset>
    <legend><?php echo $this->label("View:") ?></legend>
        <table summary="view" id="view"><tr>
        <th><input id="viewThumbs" type="radio" name="view" value="thumbs" /></th>
        <td><label for="viewThumbs">&nbsp;<?php echo $this->label("Thumbnails") ?></label> &nbsp;</td>
        <th><input id="viewList" type="radio" name="view" value="list" /></th>
        <td><label for="viewList">&nbsp;<?php echo $this->label("List") ?></label></td>
        </tr></table>
    </fieldset>
    </div>

    <div>
    <fieldset>
    <legend><?php echo $this->label("Show:") ?></legend>
        <table summary="show" id="show"><tr>
        <th><input id="showName" type="checkbox" name="name" /></th>
        <td><label for="showName">&nbsp;<?php echo $this->label("Name") ?></label> &nbsp;</td>
        <th><input id="showSize" type="checkbox" name="size" /></th>
        <td><label for="showSize">&nbsp;<?php echo $this->label("Size") ?></label> &nbsp;</td>
        <th><input id="showTime" type="checkbox" name="time" /></th>
        <td><label for="showTime">&nbsp;<?php echo $this->label("Date") ?></label></td>
        </tr></table>
    </fieldset>
    </div>

    <div>
    <fieldset>
    <legend><?php echo $this->label("Order by:") ?></legend>
        <table summary="order" id="order"><tr>
        <th><input id="sortName" type="radio" name="sort" value="name" /></th>
        <td><label for="sortName">&nbsp;<?php echo $this->label("Name") ?></label> &nbsp;</td>
        <th><input id="sortType" type="radio" name="sort" value="type" /></th>
        <td><label for="sortType">&nbsp;<?php echo $this->label("Type") ?></label> &nbsp;</td>
        <th><input id="sortSize" type="radio" name="sort" value="size" /></th>
        <td><label for="sortSize">&nbsp;<?php echo $this->label("Size") ?></label> &nbsp;</td>
        <th><input id="sortTime" type="radio" name="sort" value="date" /></th>
        <td><label for="sortTime">&nbsp;<?php echo $this->label("Date") ?></label> &nbsp;</td>
        <th><input id="sortOrder" type="checkbox" name="desc" /></th>
        <td><label for="sortOrder">&nbsp;<?php echo $this->label("Descending") ?></label></td>
        </tr></table>
    </fieldset>
    </div>

    </div>
    <div id="files">
        <div id="content"></div>
    </div>
</div>
<div id="status"><span id="fileinfo">&nbsp;</span></div>
</div>
</body>
</html>
