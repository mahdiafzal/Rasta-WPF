<?php
class Rasta_Base34_Operation
{

	static function addOneTo($number)
  {
		//if($number=='0') return '1';
  	$ns = '0123456789abcdefghijklmnopqrstuvwxyz';
  	$number_length = strlen($number);
  	if($number_length==0) return '1';
  	$right = $number[ $number_length-1 ];
  	$right_index = strpos($ns, $right);
  	$new_right = ($right_index==35)?'0':$ns[$right_index+1];
  	$right_left = ($number_length>1)?substr($number, 0, -1):'';
  	if($new_right=='0')
			return self::addOneTo($right_left).$new_right;
  	return $right_left.$new_right;
  }

}
?>
