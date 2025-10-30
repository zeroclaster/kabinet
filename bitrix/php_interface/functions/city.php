<?

class alxgeoip {
        static function GetCity(){
                
				$ip = $_SERVER['REMOTE_ADDR'];
                $arData = static::GetGeoData($ip);

                return $arData;
        }

        static function GetGeoData($ip){

                if (static::InitBots())return false;

                //$arData = static::Findingcity($ip);
				
				$arData = '';

				if (0)
				if (!$arData){										
					$ipinfo = static::get_ip_info($ip);
					$arData = $ipinfo; // город
				}
				
				if (!$arData){										
					$ipinfo = static::get_ip_info_v2($ip);
					$arData = $ipinfo; // город
				}				
				
				
                return $arData;
        }

        static function Findingcity($ip){

                $request = file_get_contents("http://api.sypexgeo.net/json/".$_SERVER['REMOTE_ADDR']);
                $array = json_decode($request);
                return $array->city->name_ru;
        }
		
		static function get_ip_info($ip)
		{
			$postData = "
				<ipquery>
					<fields>
						<all/>
					</fields>
					<ip-list>
						<ip>$ip</ip>
					</ip-list>
				</ipquery>
			"; 
		 
			$curl = curl_init(); 
		 
			curl_setopt($curl, CURLOPT_URL, 'http://194.85.91.253:8090/geo/geo.html'); 
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData); 
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		 
			$responseXml = curl_exec($curl);
			curl_close($curl);
		 
			if (substr($responseXml, 0, 5) == '<?xml')
			{
				//$ipinfo = new \SimpleXMLElement($responseXml);
				//$ipinfo = simplexml_load_string($responseXml);				
				$xml = new \CDataXML();
				$xml->LoadString($responseXml);		
				if ($node = $xml->SelectNodes('/ip-answer/ip/city')) {
						$node_ = $xml->SelectNodes('/ip-answer/ip/region');
						return array(iconv('CP1251','UTF-8',$node_->textContent()), iconv('CP1251','UTF-8',$node->textContent()));										
				}
			}
		 
			return false;
		}

		static function get_ip_info_v2($ip)
		{		 
			return ["Тульская область","Тула"];

			$request = file_get_contents("http://ip-api.com/json/".$ip."?lang=ru");     
			if (!empty($request)){
            $array = json_decode($request);
            return [$array->regionName, $array->city];
        	}
        	return false;
		}


        static function InitBots(){

                $bots = array(
                    'rambler','googlebot','ia_archiver', 'Wget', 'WebAlta','MJ12bot', 'aport','yahoo','msnbot', 'mail.ru',
                    'alexa.com', 'Baiduspider', 'Speedy Spider', 'abot', 'Indy Library' );

                foreach($bots as $bot)
                        if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false){
                                return $bot;
                        }
                return false;
        }
}