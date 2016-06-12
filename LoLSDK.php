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

	/**
	 * LoLSDK constructor.
	 * @param $lolApiKey
	 * @param $region
	 */
	private function __construct($lolApiKey, $region) {
		$this->__initVersion();
		$this->lolApi = $lolApiKey;
		if(isset($region)) {
			$this->region = $region;
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
		$summonerUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/".$this->summonerVer."/summoner/by-name/{$name}?api_key={$this->lolApi}";

		$summoner = $this->__getApi($summonerUrl);
		$summoner = $summoner->$name;
		$summoner = array(
			'id' => $summoner->id,
			'name' => $summoner->name,
			'profileIconId' => $summoner->profileIconId,
			'revisionDate' => $summoner->revisionDate,
			'summonerLevel' => $summoner->summonerLevel
		);
		return json_encode($summoner);
	}

	/**
	 * 個々のユーザのmasteryを返す
	 * @param $name
	 * @param $id
	 * @return mixed
	 */
	public function getSummonerMasteries($name, $id) {
		$summonerId = $this->getSummoner($name)["id"];
		if(isset($id)){
			$summonerId = $id;
		}
		$summonerUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/".$this->summonerVer."/summoner/{$summonerId}/masteries?api_key={$this->lolApi}";

		$data = $this->__getApi($summonerUrl);
		$masteries = $this->__parseAPISummonerMastery($data);
		echo json_encode($masteries);
	}

	/**
	 * 個々のユーザのruneを返す
	 * @param $name
	 * @return array
	 */
	public function getSummonerRunes($name, $id) {
		$summonerId = $this->getSummoner($name)["id"];
		if(isset($id)){
			$summonerId = $id;
		}
		$runeUrl = "https://{$this->region}.api.pvp.net/api/lol/{$this->region}/".$this->summonerVer."/summoner/{$summonerId}/runes?api_key={$this->lolApi}";
		$data = $this->__getApi($runeUrl);
		$runes = $this->__parseAPISummonerRune($data);
		echo json_encode($runes);
	}

	private function __parseAPISummonerMastery($data) {
		$res = array();
		foreach($data as $k => $v){
			foreach($v->pages as $ke => $val){
				foreach($val->masteries as $key => $value){
					$res[$k][$val->id]['name'] = $val->name;
					$res[$k][$val->id]['current'] = $val->current;
					$res[$k][$val->id]['masteries'][] = array(
						"id" => $value->id,
						"rank" => $value->rank
					);
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
		foreach($data as $k => $v) {
			foreach($v->pages as $ke => $val) {
				foreach($val->slots as $key => $value){
					$res[$k][$val->id]['name'] = $val->name;
					$res[$k][$val->id]['current'] = $val->current;
					$res[$k][$val->id]['masteries'][] = array(
						"runeSlotId" => $value->runeSlotId,
						"rankId" => $value->runeId
					);
				}
			}
		}
		return $res;
	}

	private function __initVersion() {
		$versions = json_decode(file_get_contents("./config/config.json"));
		$this->region = $versions->default_region;
		$this->summonerVer = $versions->summoner_version;
		$this->championVer = $versions->champion_version;
		$this->staticDataVer = $versions->static_data_version;
	}

	/**
	 * LoLのAPIを入力し、そのデータを返します。(json_decode済み)
	 * @param $url
	 * @return mixed
	 */
	private function __getApi($url) {
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
			),
		);
		$result = json_decode(file_get_contents($url, false, stream_context_create($arrContextOptions)));
		return $result;
	}
}