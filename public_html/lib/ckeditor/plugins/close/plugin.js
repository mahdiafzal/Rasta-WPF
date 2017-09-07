CKEDITOR.plugins.add( 'close',
{
	init : function( editor )
	{
		
  		var command = editor.addCommand( 'close',
										{exec : function( editor )
											{
											editor1.destroy();
											editor1	= null;
											$('#'+pageconid).html(HTMLbackuP);
											$('body').css('margin-top','26px');
											}
											});
		
		editor.ui.addButton( 'Close',
			{
				label : 'close',
				command : 'close',
				icon : this.path+'close.jpeg',
			});


	}
});