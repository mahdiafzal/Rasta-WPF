function Cconfig()
{
	return Cconfig.fn.init(); 
}
Cconfig.fn=Cconfig.prototype=
{
	baseURL:{
				js:'/js',
				css:'/css',
				img:'/flsimgs',
				rtcm:'/rtcmanager'
			},
	site:{
		id:null,
		pageCount:null,
		pageId:null
		},
	init:function()
			{
				return this;
			}
}
config=new Cconfig();
