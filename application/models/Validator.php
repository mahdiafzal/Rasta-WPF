<?php
class Application_Model_Validator
	{	
		protected $result=array();
		protected $phone_pattern="/(^\d+(\d\s-\s\d)*\d*$)/";
		protected $address_pattern="/^[\w\d]+[\s]?([\s\.\-\،][\s]?[\w\d])*/";

		public function chkPass($pass1,$pass2)
		{
			if ($pass1==$pass2)
			{
				if (strlen($pass1)>0 and strlen($pass1)<6)	{ return 'less'; }
				if (strlen($pass1)==0)				{ return 'empty'; }
				if (strlen($pass1)>5)				{ return 'correct'; }
			}
			else
			{
				return 'inCorrect';
			}
		}
//----------------------
		public function validate($frmData,$frmRule)// $frmItem is array)
		{
			$data=$frmData;
			$rule=$frmRule;
			foreach ($data as $key=>$value)
			{
				$per=explode(',', $rule[$key]); //fetch permission of each form item
				foreach ($per as $itm)
				{
					switch ($itm)
					{
						case 'notNull'				: $this->notNull		($key,$value);break;
						case 'isFarsi'				: $this->isFarsi		($key,$value);break;
						case 'isLatin'				: $this->isLatin		($key,$value);break;	
						case 'isNumber'				: $this->isNumber		($key,$value);break;	
						case 'isEmail'				: $this->isEmail		($key,$value);break;
						case 'isPhone'				: $this->isPhone		($key,$value);break;
						case 'isWebsite'			: $this->isWebsite		($key,$value);break;
						case 'isAddress'			: $this->isAddress		($key,$value);break;
						
						case 'isFarsiNumeric'		: $this->isFarsiNumeric	($key,$value);break;
						case 'isNumericFarsi'		: $this->isFarsiNumeric	($key,$value);break;
						
						case 'isLatinNumeric'		: $this->isLatinNumeric	($key,$value);break;
						case 'isNumericLatin'		: $this->isLatinNumeric	($key,$value);break;
						
						case 'isFarsiLatin'			: $this->isFarsiLatin	($key,$value);break;
						case 'isLatinFarsi'			: $this->isFarsiLatin	($key,$value);break;
						
						case 'isFarsiLatinNumeric'	: $this->isFarsiLatinNumeric	($key,$value);break;
						case 'isFarsiNumericLatin'	: $this->isFarsiLatinNumeric	($key,$value);break;
						case 'isLatinFarsiNumeric'	: $this->isFarsiLatinNumeric	($key,$value);break;
						case 'isLatinNumericFarsi'	: $this->isFarsiLatinNumeric	($key,$value);break;
						case 'isNumericLatinFarsi'	: $this->isFarsiLatinNumeric	($key,$value);break;
						case 'isNumericFarsiLatin'	: $this->isFarsiLatinNumeric	($key,$value);break;
					}								
				}
			}
		}
//----------------------
		public function validate_no______($frmData,$frmRule)// $frmItem is array)
		{
			$data=$frmData;
			$rule=$frmRule;
			$per=explode(',', $rule[$key]); //fetch permission of each form item
			foreach ($per as $itm)
			{
				switch ($itm)
				{
					case 'notNull'				: $this->notNull		($key,$value);break;
					case 'isFarsi'				: $this->isFarsi		($key,$value);break;
					case 'isLatin'				: $this->isLatin		($key,$value);break;	
					case 'isNumber'				: $this->isNumber		($key,$value);break;	
					case 'isEmail'				: $this->isEmail		($key,$value);break;
					case 'isPhone'				: $this->isPhone		($key,$value);break;
					case 'isWebsite'			: $this->isWebsite		($key,$value);break;
					case 'isAddress'			: $this->isAddress		($key,$value);break;
					
					case 'isFarsiNumeric'		: $this->isFarsiNumeric	($key,$value);break;
					case 'isNumericFarsi'		: $this->isFarsiNumeric	($key,$value);break;
					
					case 'isLatinNumeric'		: $this->isLatinNumeric	($key,$value);break;
					case 'isNumericLatin'		: $this->isLatinNumeric	($key,$value);break;
					
					case 'isFarsiLatin'			: $this->isFarsiLatin	($key,$value);break;
					case 'isLatinFarsi'			: $this->isFarsiLatin	($key,$value);break;
					
					case 'isFarsiLatinNumeric'	: $this->isFarsiLatinNumeric	($key,$value);break;
					case 'isFarsiNumericLatin'	: $this->isFarsiLatinNumeric	($key,$value);break;
					case 'isLatinFarsiNumeric'	: $this->isFarsiLatinNumeric	($key,$value);break;
					case 'isLatinNumericFarsi'	: $this->isFarsiLatinNumeric	($key,$value);break;
					case 'isNumericLatinFarsi'	: $this->isFarsiLatinNumeric	($key,$value);break;
					case 'isNumericFarsiLatin'	: $this->isFarsiLatinNumeric	($key,$value);break;
				}								
			}
		}
//----------------------
		public function getResult($itm)
			{
				$rst=$this->result;
				//print_r($rst);
				foreach($rst as $value)
					{
						if (($value[0]==$itm) & ($value[2]=='false'))
							{
								return false;
							}
					}
				return true;
			}
//----------------------
		public function setPhonePattern($pattern)
			{
				if ($pattern<>'')
					{
						$this->phone_pattern=$pattern;
					}
			}
//----------------------
		public function setAddressPattern($pattern)
			{
				if ($pattern<>'')
					{
						$this->address_pattern=$pattern;
					}
			}

//----------------------
		public function notNull($key,$val)
			{
				if (trim($val)=='')
					{
						array_push($this->result,array($key,'notNull','false'));
					}
				else
					{
						array_push($this->result,array($key,'notNull','true'));

					}
			}
//----------------------
		public function isFarsi($key,$val)
			{
				//if (!preg_match("/^[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ]+([\s]?[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ])*$/", $val))
				if (!preg_match("/^[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\s]+$/", $val))
					{
						array_push($this->result,array($key,'isFarsi','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isFarsi','true'));
					}
			}
//----------------------
		public function isLatin($key,$val)
			{
				if (!preg_match("/^[A-Za-z\s]+$/", $val))
					{
						array_push($this->result,array($key,'isLatin','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isLatin','true'));
					}
			}
//----------------------
		public function isNumber($key,$val)
		{
			if (!preg_match("/^\d+$/", $val))
				array_push($this->result,array($key,'isNumber','false'));
			else
				array_push($this->result,array($key,'isNumber','true'));
		}
//----------------------
		public function isEmail($key,$val)
			{
				if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9])+[a-zA-Z0-9\_\-]*(\.[a-zA-Z]+)+$/", $val))
					{
						array_push($this->result,array($key,'isEmail','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isEmail','true'));
					}
			}
//----------------------
		public function isPhone($key,$val)
			{
				if (!preg_match($this->phone_pattern, $val))
					{
						array_push($this->result,array($key,'isPhone','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isPhone','true'));
					}
			}
//----------------------
		public function isWebsite($key,$val)
			{
				if (!preg_match("/^[a-zA-Z0-9]+[a-zA-Z0-9\-]*(\.[a-zA-Z0-9_-]+)*(\.[a-zA-Z]+)$/", $val))
					{
						array_push($this->result,array($key,'isWebsite','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isWebsite','true'));
					}
			}
//----------------------
		public function isAddress($key,$val)
			{
				if (!preg_match($this->address_pattern, $val))
					{
						array_push($this->result,array($key,'isAddress','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isAddress','true'));
					}
			}
//----------------------	
		public function isFarsiNumeric($key,$val)
			{
				//if (!preg_match("/^[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\d]+([\s]?[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\d])*$/", $val))
				if (!preg_match("/^[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\d\s]+$/", $val))
					{
						array_push($this->result,array($key,'isFarsiNumeric','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isFarsiNumeric','true'));
					}
			}
//----------------------
		public function isLatinNumeric($key,$val)
			{
				//if (!preg_match("/^[A-Za-z\d]+([\s]?[A-Za-z\d])*$/", $val))
				if (!preg_match("/^[A-Za-z\d\s]+$/", $val))
					{
						array_push($this->result,array($key,'isLatinNumeric','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isLatinNumeric','true'));
					}
			}
//----------------------
		public function isFarsiLatin($key,$val)
			{
				//if (!preg_match("/^[A-Za-zابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ]+([\s]?[A-Za-zابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ])*$/", $val))
				if (!preg_match("/^[A-Za-zابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\s]+$/", $val))
					{
						array_push($this->result,array($key,'isFarsiLatin','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isFarsiLatin','true'));
					}
			}
//----------------------
		public function isFarsiLatinNumeric($key,$val)
			{
				//if (!preg_match("/^[A-Za-zابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\d]+([\s]?[A-Za-zابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\d])*$/", $val))
				if (!preg_match("/^[A-Za-zابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\d\s]+$/", $val))
					{
						array_push($this->result,array($key,'isFarsiLatinNumeric','false'));
					}						
				else
					{
						array_push($this->result,array($key,'isFarsiLatinNumeric','true'));
					}
			}
//----------------------
		public function checkdomain($domain)
		{
			$registry	= Zend_registry::getInstance();
			$DB			= $registry['front_db'];
			$domain		= trim($domain);
			
			$sql		= "select * from `wbs_domain` WHERE `domain`='".$domain."' ";
			$res		= $DB->fetchAll($sql);
			if ($res)// must not in Table: wbs_domain
			{
				//return 'must not in Table: wbs_domain';
				$result[]=-2;
				//return -2;
			}
		
			$arr	= split('\.' , $domain ,2);
			if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]+[a-zA-Z0-9]$/',$arr[0]))
			{
				//return 'must valid subdomain';
				$result[]=-4;
				//return -4;
			}
		
			$sql	= "select * from `tbl_black_list` WHERE `word` LIKE '%".$arr[0]."%' ";
			$res	= $DB->fetchAll($sql);
			if ($res)// in black list
			{
				//return 'must not in black list Table';
				$result[]=-1;
				//return -1;
			}
		
			$array = array(
							'com','net','org','biz','coop','info','museum','name',
							'pro','edu','gov','int','mil','ac','ad','ae','af','ag',
							'ai','al','am','an','ao','aq','ar','as','at','au','aw',
							'az','ba','bb','bd','be','bf','bg','bh','bi','bj','bm',
							'bn','bo','br','bs','bt','bv','bw','by','bz','ca','cc',
							'cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr',
							'cu','cv','cx','cy','cz','de','dj','dk','dm','do','dz',
							'ec','ee','eg','eh','er','es','et','fi','fj','fk','fm',
							'fo','fr','ga','gd','ge','gf','gg','gh','gi','gl','gm',
							'gn','gp','gq','gr','gs','gt','gu','gv','gy','hk','hm',
							'hn','hr','ht','hu','id','ie','il','im','in','io','iq',
							'ir','is','it','je','jm','jo','jp','ke','kg','kh','ki',
							'km','kn','kp','kr','kw','ky','kz','la','lb','lc','li',
							'lk','lr','ls','lt','lu','lv','ly','ma','mc','md','mg',
							'mh','mk','ml','mm','mn','mo','mp','mq','mr','ms','mt',
							'mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng',
							'ni','nl','no','np','nr','nu','nz','om','pa','pe','pf',
							'pg','ph','pk','pl','pm','pn','pr','ps','pt','pw','py',
							'qa','re','ro','rw','ru','sa','sb','sc','sd','se','sg',
							'sh','si','sj','sk','sl','sm','sn','so','sr','st','sv',
							'sy','sz','tc','td','tf','tg','th','tj','tk','tm','tn',
							'to','tp','tr','tt','tv','tw','tz','ua','ug','uk','um',
							'us','uy','uz','va','vc','ve','vg','vi','vn','vu','ws',
							'wf','ye','yt','yu','za','zm','zw');
		
			if (!in_array($arr[1],$array))
			{
				//return 'must valid postfix';
				$result[]=-5;
				//return -5;
			}
			//return 'domain is valid';
			if (count($result)!=0)
			{
				return $result;
			}
			else
			{
				return 1;
			}
		}
			//----------------------------
		public function checksubdomain($domain)
		{
			$registry	= Zend_registry::getInstance();
			$DB	= $registry['front_db'];
			$domain		= trim($domain);
			if($domain!='') 
			{
				$sql	= "select * from `tbl_black_list` WHERE `word` LIKE '%".$domain."%' ";
				$res	= $DB->fetchAll($sql);
				if ($res)// in black list
				{
					$result[]=-1;
					//return -1;
				}
			
			}
			$domainsuffix = '.'.$registry->config->base->domain; 
			$sql	= "select * from `wbs_domain` WHERE `domain`='".$domain.$domainsuffix."' ";
			$res	= $DB->fetchAll($sql);
			if ($res)// must not in Table: wbs_domain
			{
				$result[]=-2;
				//return -2;
			}
		
			if (strlen($domain)<3 or strlen($domain)>51)
			{
				$result[]=-3;
				//return -3;
			}
		
			if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]+[a-zA-Z0-9]$/',$domain))
			{
				$result[]=-4;
				//return -4;
			}
			if (count($result)!=0)
			{
				return $result;
			}
			else
			{
				return 1;
			}
		}
		
		public function hasValidLength($str, $minLenght)
		{
			if(strlen($str) >= $minLenght) return true;
			return false;
		}


	}

?>