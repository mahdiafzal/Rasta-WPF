<?php
class Application_Model_Pubcon
{

	static function get($mode=1111, $ns1='', $ns2='') 
	{
		
		if( !empty($ns1) )	$ns1 = $ns1.'.';
		if( !empty($ns2) )	$ns2 = $ns2.'.';
		else				$ns2 = $ns1;
		
		$ses = new Zend_Session_Namespace('Zend_Auth');
		$usRg	= $isAdmin	= $usID = '';
		if( is_object($ses->storage) )
		{
			$usID	= $ses->storage->id;
			//$usRg	= $ses->storage->user_group;
			$usRg	= $ses->storage->all_groups;
			$isAdmin= $ses->storage->is_admin;
		}
		
		$uc	= array('', '', '');
			
		if($mode<2000)
		{
			$wc	= array(	$ns1.'wbs_id IN (0, '.WBSiD.') AND ('.$ns1.'wbs_group RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/") ',
							$ns1.'wbs_id IN (0, '.WBSiD.') ',
							$ns1.'wbs_id ='.WBSiD.' ',
							$ns1.'wbs_id =0 '
						);
			
			if($isAdmin!=1)
			{
				$uc[1]	= ' AND ('.$ns2.'user_group RLIKE "(^0$)'. ( (empty($usRg))?'") ':'|(\/'. str_replace('/','\/)|(\/',$usRg) .'\/)") ');
				$uc[2]	= ' AND (  ('.$ns2.'users RLIKE "(^0$)'.( (!is_numeric($usID))?'" ':'|(\/'.$usID.'\/)" ').' )'
						. ' OR ('.$ns2.'user_group RLIKE "(^0$)'. ( (empty($usRg))?'") ) ':'|(\/'. str_replace('/','\/)|(\/',$usRg) .'\/)") ) ');
			}
			
			if(WBSiD==='1' and $usID==='1')
			{
				$wc	= array($ns1.'wbs_id IN (0, '.WBSiD.') ', $ns1.'wbs_id IN (0, '.WBSiD.') ', $ns1.'wbs_id IN (0, '.WBSiD.') ', $ns1.'wbs_id=0 ');
				$uc	= array('', '', '');
			}
			switch($mode)
			{
				case 1111: return (' ('.$wc[0].$uc[1].') ');break;	// wbs, wbs_group, user 
				case 1112: return (' ('.$wc[0].$uc[2].') ');break;	// wbs, wbs_group, user 
				case 1110: return (' ('.$wc[0].$uc[0].') ');break;	// wbs, wbs_group, 
				case 1101: return (' ('.$wc[1].$uc[1].') ');break;	// wbs, , user 
				case 1102: return (' ('.$wc[1].$uc[2].') ');break;	// wbs, , user 
				case 1100: return (' ('.$wc[1].$uc[0].') ');break;	// wbs, ,  
				case 1000: return (' ('.$wc[2].$uc[0].') ');break;	// , ,  
				case 1010: return (' ('.$wc[3].$uc[0].') ');break;	// , ,  
				case 1001: return (' ('.$wc[2].$uc[1].') ');break;	// , , user 
				case 1002: return (' ('.$wc[2].$uc[2].') ');break;	// , , user 
			}
		}
		elseif($mode>=2000 and $mode<3000)
		{
			if($isAdmin==0)
				$uc[2]	= ' '.$ns2.'user_group RLIKE "(^0$)'. ( (empty($usRg))?'" ':'|(\/'. str_replace('/','\/)|(\/',$usRg) .'\/)" ');
			elseif($isAdmin==1)
				$uc[2]	= '1';
			else
				$uc[2]	= '0';

			switch($mode)
			{
				case 2001: return ( ' ('.$uc[2].') ');break;	
			}
		}

	}
	
}	

?>
