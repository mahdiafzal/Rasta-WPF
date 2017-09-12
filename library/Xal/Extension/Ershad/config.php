<?php

$query['FARAKHANAZMOON'] = 
array(
	'acl'=> array('staff'),
	'refrence'=> array(),
	'statement'=>array(
					 '$and'=>array(
							'OrganizationInfos.Roles.ResponsibleDirector'=>ture,
							'OrganizationInfos.OfficalTests.FirstTest.score'=>50,
							'OrganizationInfos.OfficalTests.FirstTest.invite'=>true
							)
						)
);
$config = array('query'=>$query);
?>
