<?php


namespace Motus\Tools\Block;


trait MotusLocationTrait
{
    protected $_DBA;
    /**
     * Method to get a google map script
     * @param string $callBack
     * @return \Tina4\HTMLElement
     */
    public function getGoogleMapsScript($callBack="initMap") {
        $apiKey = $this->getData("apiKey");
        return _script(["src" => "https://maps.googleapis.com/maps/api/js?key={$apiKey}&callback={$callBack}&libraries=&v=weekly", "async"]);
    }

    /**
     * Initialize Tina4
     */
    public function initTina4() {
        if (!defined("TINA4_INCLUDE_LOCATIONS")) {
            define("TINA4_INCLUDE_LOCATIONS", ["./system/src/objects"]);
        }
        $env = include ("./app/etc/env.php");

        global $DBA;
        $DBA = new \Tina4\DataMySQL($env["db"]["connection"]["default"]["host"] . ":" . $env["db"]["connection"]["default"]["dbname"], $env["db"]["connection"]["default"]["username"], $env["db"]["connection"]["default"]["password"]);
        $this->_DBA = $DBA;

    }

    /**
     * Gets the location based on an address
     * @param $locationString
     * @return mixed
     */
    public function getLocation($locationString) {

        $this->initTina4();

        $motusLocation = new \MotusLocation();
        if ($motusLocation->find("lookup = '{$locationString}'")) {
            $json = unserialize($motusLocation->response);
        } else {
            $motusLocation->lookup = $locationString;

            $url = $this->_locationUrl . "?address=" . urlencode($locationString) . "&key=" . $this->getData('apiKey');

            $json = @json_decode(file_get_contents($url));

            $motusLocation->response = serialize($json);
            $motusLocation->save();
        }
        return $json;
    }

    /**
     * Gets the closet town based on IP address
     * @param null $ip
     * @param string $purpose
     * @param bool $deep_detect
     * @return array|string|null
     */
    public function getIpInfo($ip = NULL, $purpose = "location", $deep_detect = TRUE) {


        if (empty($ip)) $ip = $_SERVER["REMOTE_ADDR"];
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }


        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->geoplugin_city,
                            "state"          => @$ipdat->geoplugin_regionName,
                            "country"        => @$ipdat->geoplugin_countryName,
                            "country_code"   => @$ipdat->geoplugin_countryCode,
                            "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }

        return $output;
    }
}
