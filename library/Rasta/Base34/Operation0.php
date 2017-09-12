<?php
class Rasta_Base34_Operation
{

	static function addOneTo($number)
  {
  	$ns = '123456789abcdefghijklmnopqrstuvwxyz';
  	$number_length = strlen($number);
  	if($number_length==0) return '1';
  	$right = $number[ $number_length-1 ];
  	$right_index = strpos($ns, $right);
  	$new_right = ($right_index==34)?'0':$ns[$right_index+1];
  	$right_left = ($number_length>1)?substr($number, 0, -1):'';
  	if($new_right=='0')
  		return addOneTo($right_left).$new_right;
  	return $right_left.$new_right;
  }

}
?>
