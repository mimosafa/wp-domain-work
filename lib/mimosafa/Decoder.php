<?php
namespace mimosafa;

/**
 * Decoder, php array and json data to DOM element
 * Folked from 'wakisuke/Decoder.php'
 * 
 * @see https://gist.github.com/wakisuke/8000861
 */
trait Decoder {
	
	/**
	 * @param  string $jsondata_string (required) json data
	 * @return string html string
	 */
	public function getJsonToHtmlString($jsondata_string) {
		return $this -> getArrayToHtmlString(json_decode($jsondata_string, TRUE));
	}
	
	/**
	 * @param  array @arraydata_array (required)
	 */
	public function getArrayToHtmlString($arraydata_array) {
		$getDomDomelement = function($dom_array, $root_domdocument) use (&$getDomDomelement) {
			$domelement_domelement = NULL;
			
			if (isset($dom_array['element'])) {
				if (isset($dom_array['text']))
					$domelement_domelement = $root_domdocument -> createElement($dom_array['element'], $dom_array['text']);
				else
					$domelement_domelement = $root_domdocument -> createElement($dom_array['element']);
				
				if (isset($dom_array['attribute'])) {
					foreach ($dom_array['attribute'] as $attributekey_string => $attributevalue_string) {
						$domattr_domdocument = $root_domdocument -> createAttribute($attributekey_string);
						$domattr_domdocument -> value = $attributevalue_string;
						
						$domelement_domelement -> appendChild($domattr_domdocument);
					}
				}
				
				if (isset($dom_array['children'])) {
					foreach ($dom_array['children'] as $childrendom_array)
						$domelement_domelement -> appendChild($getDomDomelement($childrendom_array, $root_domdocument));
				}
			}
			
			if (isset($domelement_domelement))
				$root_domdocument -> appendChild($domelement_domelement);
			
			return $domelement_domelement;
		};
		
		$main = function($arraydata_array) use ($getDomDomelement) {
			$root_domdocument = new \DOMDocument();
			
			if (is_array($arraydata_array))
			foreach ($arraydata_array as $value) {
				if (isset($value['element'])) {
					$root_domdocument -> appendChild($getDomDomelement($value, $root_domdocument));
				}
			}
			
			return $root_domdocument;
		};
		
		return $main($arraydata_array) -> saveHTML();
	}

}
