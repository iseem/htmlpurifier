<?php

class HTMLPurifier_Filter_MyIframe extends HTMLPurifier_Filter
{

    public $name = 'MyIframe';

	public function preFilter($html, $config, $context) {
	    return preg_replace("/<iframe/", "<img class=\"MyIframe\" ", preg_replace("/<\/iframe>/", "", $html));
	}

    public function postFilter($html, $config, $context) {
       $post_regex = '#<img class="MyIframe" ([^>]+)>#';
        return preg_replace_callback($post_regex, array($this, 'postFilterCallback'), $html);
    }

    protected function postFilterCallback($matches) {
		
		// remove the trailing slash bug
		$matches[1] = trim(rtrim($matches[1], "/"));

		// if this is a YOUTUBE iframe we need to add wmode=transparent 
		// to the querystring to fix the youtube z-index bug
		if(preg_match("/youtube/i", $matches[1])){

			// extract the src attribute 
			preg_match("/ *[src]*= *[\"\']{0,1}([^\"\'\ >]*)/i", $matches[1], $myPregMatches);

			// separate the querystring
			$myUrl = explode('?', $myPregMatches[1]);

			// if there's already a querystring
			if($myUrl[1] != ''){
				// if there's no wmode set, add it
				if(!preg_match("/wmode=transparent/", $myUrl[1])){
					$myNewUrl = $myUrl[0].'?'.$myUrl[1]."&amp;wmode=transparent";
				}
				// otherwise leave the url alone
				else{
					$myNewUrl = $myUrl[0].'?'.$myUrl[1];
				}	
			// if there's NOT a querystring, add one
			}else{
				$myNewUrl = $myUrl[0]."?wmode=transparent";
			}

			$finalReplace = str_replace($myPregMatches[1], $myNewUrl, $matches[1]);
			return '<iframe '.$finalReplace.'></iframe>';

		}else{
			// if this is not a youtube video
			return '<iframe '.$matches[1].'></iframe>';
		}

    }
}

