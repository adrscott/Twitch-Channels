<?php

/**
 * Class Application
 * The heart of the application
 * @author adrscott (twitter.com/adrscott, adrscott.com)
 */
class Application
{

	/**
	* The twitch team name
	*
	* @var string
	*/
	private $_teamname = 'esl';

	/**
	* The main twitch base url for channels
	*
	* @var string
	*/
	private $_apibase = 'https://api.twitch.tv/kraken/streams?channel=';

	/**
	* The path to the cache file folder
	*
	* @var string
	*/
	private $_cachepath = 'cache/';

	/**
	* The name of the default cache file
	*
	* @var string
	*/
	private $_cachename = 'channels';

	/**
	* The cache file extension
	*
	* @var string
	*/
	private $_extension = '.cache';


	public function getChannelsCache()
	{
		if (file_exists($this->getChannelsCacheDir()) && (filemtime($this->getChannelsCacheDir()) > (time() - 60 * 5 ))) {
		   // Cache file is less than five minutes old. Don't bother refreshing, just use the file as-is.
			return json_decode(file_get_contents($this->getChannelsCacheDir()), true);
		} else {
			// Our cache is out-of-date, so refresh the data from the API url, and also save it over our cache for next time.
			$get_data = json_decode($this->getTeamCache(), true);
			$get_data = implode(",", $get_data);
			$get_data = file_get_contents($this->_apibase.rawurlencode($get_data));
			file_put_contents($this->getChannelsCacheDir(), $get_data, LOCK_EX);
			return json_decode($get_data, true);
		}
	}

	public function getTeamCache()
	{
		if (file_exists($this->getTeamCacheDir()) && (filemtime($this->getTeamCacheDir()) > (time() - 60 * 60 ))) {
		   // Cache file is less than 1 hour old, use it.
			return file_get_contents($this->getTeamCacheDir());
		} else {
			// Our team cache is out-of-date. Scrap the data from the twitch team page, and also save it over our cache for next time.
			file_put_contents($this->getTeamCacheDir(), json_encode($this->scrapTeamChannels()), LOCK_EX);
			// Return the newly created cache file.
			return file_get_contents($this->getTeamCacheDir());
		}
	}	

	private function scrapTeamChannels() 
	{
		$html = new DOMDocument();
		$html->loadHtmlFile('https://www.twitch.tv/team/'.$this->_teamname.'/live_member_list?page=1');

		$xpath = new DOMXpath($html);
		$nodelist = $xpath->query('//div[@class="page_data"]//a/@href');

		if($nodelist->length > 1) {
			$pages = array();
			foreach ($nodelist as $n) {
				$format = filter_var($n->nodeValue, FILTER_SANITIZE_NUMBER_INT);
				array_push($pages, $format);
			}
			$total_pages = max($pages); 
		} else {
			$total_pages = 1; 
		}

		unset($html);
		unset($xpath);

		$members = array();
		for($i = 1; $i <= $total_pages; $i++) 
		{
			$htmlContent = file_get_contents('https://www.twitch.tv/team/'.$this->_teamname.'/live_member_list?page='.$i);
			$doc = new DOMDocument();
			libxml_use_internal_errors(true);
			$doc->loadHTML($htmlContent);

			$xpath = new DOMXpath($doc);
			$get_members = $xpath->query('//span[@class="member_name"]');

			foreach($get_members as $member) {
				array_push($members, $member->nodeValue);
			}

		}

		return $members;
	}

	private function getChannelsCacheDir() {
		if (true === $this->checkCacheDir()) {
		  return $this->_cachepath . $this->_cachename . $this->_extension;
		}
	}

	private function getTeamCacheDir() {
		if (true === $this->checkCacheDir()) {
		  return $this->_cachepath . $this->_teamname . $this->_extension;
		}
	}	

	private function checkCacheDir() {
		if (!is_dir($this->_cachepath) && !mkdir($this->_cachepath, 0775, true)) {
			throw new Exception('Unable to create cache directory ' . $this->_cachepath);
		} elseif (!is_readable($this->_cachepath) || !is_writable($this->_cachepath)) {
			if (!chmod($this->_cachepath, 0775)) {
				throw new Exception($this->_cachepath . ' must be readable and writeable');
			}
		}
		return true;
	}	

}