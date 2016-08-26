<?php

/**
 * User: LustyRain
 * Date: 2016/06/12
 */
class LoLSDK {

	public $region = "";
	private $summonerVer = "";
	private $championVer = "";
	private $staticDataVer = "";
	private $gameVer = "";
	private $statsVer = "";
	private $matchlistVer = "";
	private $matchVer = "";

	/**
	 * LoLSDK constructor.
	 * @param $lolApiKey
	 * @param $region
	 */
	public function __construct($lolApiKey, $region) {
		$this->__initVersion();
		$this->lolApi = $lolApiKey;
		if (isset($region)) {
			$this->setRegion($region);
		}
	}

	public function setRegion($region = "na") {
		$this->region = $region;
	}

	/**
	 * summonerのデータを返す。ここをメインに使用し、summonerIdを取得する
	 * @param $name
	 * @return array|mixed|null
	 */
	public function getSummoner($name) {
		$summoner = null;
		$name = mb_strtolower($name);
		$summonerUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/" . $this->summonerVer . "/summoner/by-name/{$name}?api_key={$this->lolApi}";

		$summoner = $this->__getApi($summonerUrl);
		if ($this->__validateUtf8($name)) {
			$name = urldecode($name);
		}
		$summoner = $summoner->$name;
		$summoner = array('id' => $summoner->id, 'name' => $summoner->name, 'profileIconId' => $summoner->profileIconId, 'revisionDate' => $summoner->revisionDate, 'summonerLevel' => $summoner->summonerLevel);
		return json_encode($summoner);
	}

	/**
	 * 個々のユーザのmasteryを返す
	 * @param $name
	 * @param $id
	 * @return mixed
	 */
	public function getSummonerMasteries($name, $id = null) {
		if (isset($id)) {
			$summonerId = $id;
		} else {
			$summonerId = json_decode($this->getSummoner($name))->id;
		}
		$summonerUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/" . $this->summonerVer . "/summoner/{$summonerId}/masteries?api_key={$this->lolApi}";

		$data = $this->__getApi($summonerUrl);
		$masteries = $this->__parseAPISummonerMastery($data);
		echo json_encode($masteries);
	}

	/**
	 * 個々のユーザのruneを返す
	 * @param $name
	 * @param $id
	 * @return array
	 */
	public function getSummonerRunes($name,  $id = null) {
		if (isset($id)) {
			$summonerId = $id;
		} else {
			$summonerId = json_decode($this->getSummoner($name))->id;
		}
		$runeUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/" . $this->summonerVer . "/summoner/{$summonerId}/runes?api_key={$this->lolApi}";
		$data = $this->__getApi($runeUrl);
		$runes = $this->__parseAPISummonerRune($data);
		echo json_encode($runes);
	}

	/**
	 * サモナーネームを返す
	 * @param $id
	 * @return mixed
	 */
	public function getSummonerName($id) {
		$summonerId = $id;
		$runeUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/" . $this->summonerVer . "/summoner/{$summonerId}/name?api_key={$this->lolApi}";
		return $data = $this->__getApi($runeUrl);
	}

	/**
	 * チャンピオンの情報を返す
	 * @param $championId
	 * @return mixed
	 */
	public function getChampion($championId) {
		$championUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/{$this->championVer}/champion?api_key={$this->lolApi}";
		if(isset($championId)){
			$championUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/{$this->championVer}/champion/{$championId}?api_key={$this->lolApi}";
		}
		return $data = $this->__getApi($championUrl);
	}

	/**
	 * チャンピオンのマスタリーを返す
	 * @param $name
	 * @param $id
	 * @param $championId
	 * @return mixed
	 */
	public function getChampionMastery($name, $id, $championId) {
		if (isset($id)) {
			$summonerId = $id;
		} else {
			$summonerId = json_decode($this->getSummoner($name))->id;
		}
		$url = "https://{$this->region}.api.pvp.net/championmastery/location/{$this->region}/player/{$summonerId}/champion/{$championId}?api_key={$this->lolApi}";
		return $data = $this->__getApi($url);
	}

	/**
	 * ゲーム情報取得
	 * @param $name
	 * @param null $id
	 * @return mixed
	 */
	public function getGame($name,  $id = null) {
		if (isset($id)) {
			$summonerId = $id;
		} else {
			$summonerId = json_decode($this->getSummoner($name))->id;
		}
		$url = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/{$this->gameVer}/game/by-summoner/{$summonerId}/recent?api_key={$this->lolApi}";
		return $data = $this->__getApi($url);
	}

	/**
	 * マッチ情報取得
	 * @param $matchId
	 * @return mixed
	 */
	public function getMatch($matchId) {
		$url = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/{$this->matchVer}/match/{$matchId}?api_key={$this->lolApi}";
		return $data = $this->__getApi($url);
	}

	/**
	 * マッチリスト取得
	 * @param $name
	 * @param null $id
	 * @return mixed
	 */
	public function getMatchList($name, $id = null) {
		if (isset($id)) {
			$summonerId = $id;
		} else {
			$summonerId = json_decode($this->getSummoner($name))->id;
		}
		$url = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/{$this->matchlistVer}/matchlist/by-summoner/{$summonerId}?api_key={$this->lolApi}";
		return $data = $this->__getApi($url);
	}

	/**
	 * ステータス取得
	 * @param $name
	 * @param null $id
	 * @return mixed
	 */
	public function getStats($name, $id=null) {
		if (isset($id)) {
			$summonerId = $id;
		} else {
			$summonerId = json_decode($this->getSummoner($name))->id;
		}
		$url = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/{$this->statsVer}/stats/by-summoner/{$summonerId}/summary?api_key={$this->lolApi}";
		return $data = $this->__getApi($url);
	}

	private function __initVersion() {
		$versions = json_decode(file_get_contents("./config/config.json"));
		$this->region = $versions->default_region;
		$this->summonerVer = $versions->summoner_version;
		$this->championVer = $versions->champion_version;
		$this->staticDataVer = $versions->static_data_version;
		$this->gameVer = $versions->game_version;
		$this->matchVer = $versions->match_version;
		$this->statsVer = $versions->stats_version;
		$this->matchlistVer = $versions->match_list_version;
	}



	/**
	 * LoLのAPIを入力し、そのデータを返します。(json_decode済み)
	 * @param $url
	 * @return mixed
	 */
	private function __getApi($url) {
		$arrContextOptions = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false,),);
		$result = json_decode(file_get_contents($url, false, stream_context_create($arrContextOptions)));
		return $result;
	}

	private function __parseAPISummonerMastery($data) {
		$res = array();
		foreach ($data as $k => $v) {
			foreach ($v->pages as $ke => $val) {
				foreach ($val->masteries as $key => $value) {
					$res[$k][$val->id]['name'] = $val->name;
					$res[$k][$val->id]['current'] = $val->current;
					$res[$k][$val->id]['masteries'][] = array("id" => $value->id, "rank" => $value->rank);
				}
			}
		}
		return $res;
	}

	/**
	 * SummonerRuneのAPIを整形
	 * @param $data
	 * @return array
	 */
	private function __parseAPISummonerRune($data) {
		$res = array();
		foreach ($data as $k => $v) {
			foreach ($v->pages as $ke => $val) {
				foreach ($val->slots as $key => $value) {
					$res[$k][$val->id]['name'] = $val->name;
					$res[$k][$val->id]['current'] = $val->current;
					$res[$k][$val->id]['masteries'][] = array("runeSlotId" => $value->runeSlotId, "rankId" => $value->runeId);
				}
			}
		}
		return $res;
	}


	private function __validateUtf8() {
		return (bool)preg_match('//u', serialize(func_get_args()));
	}
}