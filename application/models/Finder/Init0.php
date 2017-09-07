<?php
class Application_Model_Finder_Init
{
	public function Authentic()
	{
		if(!isset($_SESSION)) session_start();
		$errorMSG	= '<h1>ACCESS DENIED</h1>';
		if(empty($_SESSION['Zend_Auth']['storage'])) die($errorMSG);
		if($_SESSION['MyApp']['WBSiD'] != $_SESSION['Zend_Auth']['storage']->wb_user_id) die($errorMSG);
		if(empty($_SESSION['MyApp']['hostSize']) or $_SESSION['MyApp']['hostSize']==0) die($errorMSG);
		return true;
	}
	public function configKCFinder()
	{
		if(!isset($_SESSION['KCFINDER']))	$_SESSION['KCFINDER'] = array();
		$target	= $_SESSION['MyApp']['WBSiD'] ;
		$_SESSION['KCFINDER']['disabled'] = false;
		$_SESSION['KCFINDER']['uploadURL'] = "/flsimgs/".$target;
		$_SESSION['KCFINDER']['uploadDir'] = "../flsimgs/".$target;
		
		$_SESSION['KCFINDER']['access']	= array(
			'files' => array(
				'upload' => false,
				'delete' => true,
				'copy' => true,
				'move' => true,
				'rename' => true
			),
		
			'dirs' => array(
				'create' => true,
				'delete' => true,
				'rename' => true
			)
		);
		
		if(!empty($_SESSION['MyApp']['hostSize']) and $_SESSION['MyApp']['hostSize']>0)
		{
			$hostSize	= $_SESSION['MyApp']['hostSize'];
			$path= "../flsimgs/".$target; 
			$ar= $this->getDirectorySize($path); 
			if(round($ar['size']/(1024*1024),1) < $hostSize) $_SESSION['KCFINDER']['access']['files']['upload'] = true;
		}
		
	}
	public function getDirectorySize($path) 
	{ 
	  $totalsize = 0; 
	  $totalcount = 0; 
	  $dircount = 0; 
	  if ($handle = opendir ($path)) 
	  { 
		while (false !== ($file = readdir($handle))) 
		{ 
		  $nextpath = $path . '/' . $file; 
		  if ($file != '.' && $file != '..' && !is_link ($nextpath)) 
		  { 
			if (is_dir ($nextpath)) 
			{ 
			  $dircount++; 
			  $result = $this->getDirectorySize($nextpath); 
			  $totalsize += $result['size']; 
			  $totalcount += $result['count']; 
			  $dircount += $result['dircount']; 
			} 
			elseif (is_file ($nextpath)) 
			{ 
			  $totalsize += filesize ($nextpath); 
			  $totalcount++; 
			} 
		  } 
		} 
	  } 
	  closedir ($handle); 
	  $total['size'] = $totalsize; 
	  $total['count'] = $totalcount; 
	  $total['dircount'] = $dircount; 
	  return $total; 
	} 

	public function sizeFormat($size) 
	{ 
		if($size<1024) 
		{ 
			return $size." bytes"; 
		} 
		else if($size<(1024*1024)) 
		{ 
			$size=round($size/1024,1); 
			return $size." KB"; 
		} 
		else if($size<(1024*1024*1024)) 
		{ 
			$size=round($size/(1024*1024),1); 
			return $size." MB"; 
		} 
		else 
		{ 
			$size=round($size/(1024*1024*1024),1); 
			return $size." GB"; 
		} 
	
	}  
}

?>