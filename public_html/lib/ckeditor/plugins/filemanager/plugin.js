CKEDITOR.plugins.add( 'filemanager',
{
	init : function( editor )
	{
		

  		var command = editor.addCommand( 'filemanager',
										{exec : function( editor )
											{
											window.open('/rastakCMS/admin/code/kcfinder/browse.php?type=files', 
														'jav',
														'width=600,height=400,resizable=no'
														);
											}, 
											async : true});
		
		editor.ui.addButton( 'Filemanager',
			{
				label : 'file manager',
				command : 'filemanager',
				icon : this.path+'folder.gif',
			});


	}
});