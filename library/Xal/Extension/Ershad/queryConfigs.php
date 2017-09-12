<?php

	$queryConfig['kanoonBaseInfos'] = array(
		'query' => array(

		),
		'fields' => array(
			"BaseInfos" => true,
			"FormData" => true,
			"Metadata" => true
		)
	);

	$queryConfig['kanoonPersonsList'] = array(
		'query' => array(

		),
		'fields' => array(

		)
	);

	$queryConfig['confirmationForm'] = array(
		'query' => array(
		),
		'fields' => array(
		)
	);

	$queryConfig['infoconfirm'] = array(
		'query' => array(
			"FormData.formStatus" => "true"
		),
		'fields' => array(
		)
	);

	


?>