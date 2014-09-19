<?php

//header("Content-Type: Application/json");

// fill in your consumer key and consumer secret below
define('CONSUMER_KEY', 'NRxdoTj2XvFrUbZNzOe5A');
define('CONSUMER_SECRET', '6CQINAoUsRhkGUfuRYo5EEPGGCF8Fjj5nwKJ4SQkxI');
define('PARSE_APP_ID', 'miKyV4x6Y5T17s7g1xrReHrNzyA2at3Sj2iCJqR5');
define('PARSE_REST_API', 'ncDk1WY27W3EdxkJOdsJuCnEZXfcUbf9MOGcEtIa');
define('PARSE_API_URL', "https://api.parse.com/1/");

$router = stripslashes(trim($_GET["route"]));


/**
*	Get the Bearer Token, this is an implementation of steps 1&2
*	from https://dev.twitter.com/docs/auth/application-only-auth
*/
function get_bearer_token() {
	$encoded_consumer_key = urlencode(CONSUMER_KEY);
	$encoded_consumer_secret = urlencode(CONSUMER_SECRET);
	$bearer_token = $encoded_consumer_key.':'.$encoded_consumer_secret;
	$base64_encoded_bearer_token = base64_encode($bearer_token);
	$url = "https://api.twitter.com/oauth2/token"; // url to send data to for authentication
	$headers = array( 
		"POST /oauth2/token HTTP/1.1", 
		"Host: api.twitter.com", 
		"User-Agent: joetorraca Twitter Application-only OAuth App v.1",
		"Authorization: Basic ".$base64_encoded_bearer_token."",
		"Content-Type: application/x-www-form-urlencoded;charset=UTF-8", 
		"Content-Length: 29"
	); 

	// Configure CURL request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
	$header = curl_setopt($ch, CURLOPT_HEADER, 1); // send custom headers
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$retrievedhtml = curl_exec ($ch); // execute the curl
	curl_close($ch); // close the curl
	$output = explode("\n", $retrievedhtml);
	$bearer_token = '';
	foreach($output as $line)
	{
		if($line === false)
		{
		} else {
			$bearer_token = $line;
		}
	}
	$bearer_token = json_decode($bearer_token);
	return $bearer_token->{'access_token'};
}

function invalidate_bearer_token($bearer_token){
	$encoded_consumer_key = urlencode(CONSUMER_KEY);
	$encoded_consumer_secret = urlencode(CONSUMER_SECRET);
	$consumer_token = $encoded_consumer_key.':'.$encoded_consumer_secret;
	$base64_encoded_consumer_token = base64_encode($consumer_token);
	// step 2
	$url = "https://api.twitter.com/oauth2/invalidate_token"; // url to send data to for authentication
	$headers = array( 
		"POST /oauth2/invalidate_token HTTP/1.1", 
		"Host: api.twitter.com", 
		"User-Agent: joetorraca Twitter Application-only OAuth App v.1",
		"Authorization: Basic ".$base64_encoded_consumer_token."",
		"Accept: */*", 
		"Content-Type: application/x-www-form-urlencoded", 
		"Content-Length: ".(strlen($bearer_token)+13)
	); 
    
	$ch = curl_init();  // setup a curl
	curl_setopt($ch, CURLOPT_URL,$url);  // set url to send to
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set custom headers
	curl_setopt($ch, CURLOPT_POST, 1); // send as post
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output
	curl_setopt($ch, CURLOPT_POSTFIELDS, "access_token=".$bearer_token.""); // post body/fields to be sent
	$header = curl_setopt($ch, CURLOPT_HEADER, 1); // send custom headers
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$retrievedhtml = curl_exec ($ch); // execute the curl
	curl_close($ch); // close the curl
	return $retrievedhtml;
}

function search_for_a_term($bearer_token, $query, $result_type='mixed', $count='15'){
	$url = "https://api.twitter.com/1.1/search/tweets.json"; // base url
	$q = urlencode(trim($query)); // query term
	$formed_url ='?q='.$q; // fully formed url
	if($result_type!='mixed'){$formed_url = $formed_url.'&result_type='.$result_type;} // result type - mixed(default), recent, popular
	if($count!='15'){$formed_url = $formed_url.'&count='.$count;} // results per page - defaulted to 15
	$formed_url = $formed_url.'&include_entities=false'; // makes sure the entities are included, note @mentions are not included see documentation
	$headers = array( 
		"GET /1.1/search/tweets.json".$formed_url." HTTP/1.1", 
		"Host: api.twitter.com", 
		"User-Agent: joetorraca Twitter Application-only OAuth App v.1",
		"Authorization: Bearer ".$bearer_token."",
	);
	$ch = curl_init();  // setup a curl
	curl_setopt($ch, CURLOPT_URL,$url.$formed_url);  // set url to send to
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // set custom headers
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output
	$retrievedhtml = curl_exec ($ch); // execute the curl
	curl_close($ch); // close the curl
	return $retrievedhtml;
}

function insertData($class, $tweetID, $tweetText, $tweetPostedAt, $tweetUserID, $tweetUsername, $sentimentType, $sentimentScore, $hashtag)
{

	if (!$class || !$tweetID || !$tweetText || !$tweetPostedAt || !$tweetUserID || !$tweetUsername || !$sentimentType || !$sentimentScore) {
		echo "MISSING FIELD";
		return false;
	}

	$parseURLExtension = "classes/".$class."/";

	$dataArray = Array(
		"Hashtag"=>$hashtag,
		"Tweet_id"=>$tweetID,
		"Tweet_text"=>$tweetText,
		"Tweet_postedAt"=>$tweetPostedAt,
		"Tweet_userID"=>$tweetUserID,
		"Tweet_username"=>$tweetUsername,
		"Sentiment_type"=>$sentimentType,
		"Sentiment_score"=>$sentimentScore
	);
	$insertData = json_encode($dataArray);

	$dR = curl_init();
	curl_setopt($dR, CURLOPT_URL, PARSE_API_URL.$parseURLExtension);
	curl_setopt($dR, CURLOPT_POST, true);
	curl_setopt($dR, CURLOPT_POSTFIELDS, $insertData);
	curl_setopt($dR, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($dR, CURLOPT_HTTPHEADER, Array("X-Parse-Application-Id:".PARSE_APP_ID, "X-Parse-REST-API-Key:".PARSE_REST_API, "Content-Type: application/json"));
	$success = curl_exec($dR);
	curl_close($dR);
}

function insertDataWithLargeArray($containmentArray)
{

	if (!$containmentArray) {
		$error = Array(
			"error"=>true,
			"results"=>false,
			"problem"=>"FUNCTION_MISSING_DATA"
		);
		print_r(json_encode($error));
		exit;
	}

	$batchURL = "batch";

	$dR = curl_init();
	curl_setopt($dR, CURLOPT_URL, PARSE_API_URL.$batchURL);
	curl_setopt($dR, CURLOPT_POST, true);
	curl_setopt($dR, CURLOPT_POSTFIELDS, json_encode($containmentArray));
	curl_setopt($dR, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($dR, CURLOPT_HTTPHEADER, Array("X-Parse-Application-Id:".PARSE_APP_ID, "X-Parse-REST-API-Key:".PARSE_REST_API, "Content-Type: application/json"));
	$success = curl_exec($dR);
	curl_close($dR);
}

function loadData($key, $class)
{
	if (!$key || !$class) {
		$missingField = Array(
			"error"=>true,
			"results"=>false,
			"problem"=>"FUNCTION_MISSING_FIELD"
		);
		echo json_encode($missingField);
		exit;
	}

	$parseURLExtension = "classes/".$class;

	$lr = curl_init();
	curl_setopt($lr, CURLOPT_HTTPHEADER, Array("X-Parse-Application-Id:".PARSE_APP_ID, "X-Parse-REST-API-Key:".PARSE_REST_API, "Content-Type: application/json"));
	curl_setopt($lr, CURLOPT_URL, PARSE_API_URL.$parseURLExtension.'?where=' . json_encode(array("Hashtag"=>urlencode($key))));
	curl_setopt($lr, CURLOPT_RETURNTRANSFER, TRUE);
	$data = curl_exec($lr);
	curl_close($lr);

	$encoded;
	$data;

	if (!$data || $data == "" || $data == '{"results":[]}') {
		$bearer_token = get_bearer_token();

		$searchData = search_for_a_term($bearer_token, $key);

		$prop = get_object_vars(json_decode($searchData));

		$tweetArray = Array();

		$parseDataArray = Array(
			"requests"=>Array()
		);

		foreach($prop["statuses"] as $tweet)
		{
			$tweetText = urlencode($tweet->text);
			$alAPIKey = "2094dd01fd7cbceb7e1bb916840e40e81f25d16f";
			$alURL = "http://access.alchemyapi.com/calls/text/TextGetTextSentiment?apikey=".$alAPIKey."&text=".$tweetText."&outputMode=json";
			$al = curl_init();
			curl_setopt($al, CURLOPT_URL, $alURL);
			curl_setopt($al, CURLOPT_RETURNTRANSFER, true);
			$alchemy = curl_exec($al);
			curl_close($al);

			$alData = get_object_vars(json_decode($alchemy));

			if (($alData["docSentiment"]->type != "neutral" && $alData["docSentiment"]->type != null) && $alData["docSentiment"]->score != null) {

				$tempTweetArray = Array(
					"text"=>$tweet->text,
					"data"=>Array(
						"userID"=>$tweet->user->id,
						"userName"=>$tweet->user->screen_name,
						"utc_offset"=>$tweet->user->utc_offset,
						"time_zone"=>$tweet->user->time_zone,
						"profile_image"=>$tweet->user->profile_image_url_https,
						"tweetID"=>$tweet->id,
						"postedAt"=>$tweet->created_at,
						"hashtag"=>$key
					),
					"sentiment"=>Array(
						"type"=>$alData["docSentiment"]->type,
						"score"=>$alData["docSentiment"]->score
					)
				);
				array_push($tweetArray, $tempTweetArray);

				$parseDataArray["requests"][] = Array(
					"method"=>"POST",
					"path"=>"/1/classes/tweets",
					"body"=>Array(
						"Hashtag"=>$key,
						"Tweet_id"=>$tweet->id,
						"Tweet_text"=>$tweet->text,
						"Tweet_postedAt"=>$tweet->created_at,
						"Tweet_userID"=>$tweet->user->id,
						"Tweet_username"=>$tweet->user->screen_name,
						"Sentiment_type"=>$alData["docSentiment"]->type,
						"Sentiment_score"=>$alData["docSentiment"]->score
					)
				);
			}
		}

		insertDataWithLargeArray($parseDataArray);

		invalidate_bearer_token($bearer_token);

		$data = loadData($key, "tweets");

		$unencoded = $data;

		$encoded = json_encode($data);

	}

	$sp = curl_init();
	curl_setopt($sp, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
	curl_setopt($sp, CURLOPT_POST, 1);
	curl_setopt($sp, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($sp, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($sp, CURLOPT_URL, "67.194.35.25:5000/");
	//$python = curl_exec($sp);
	curl_close($sp);

	//print_r($python);

	print_r($data);
}

/**
 * ROUTING MECHANISM
 */

switch ($router) {

	case "pull":

		$pullQuery = urldecode(stripslashes(trim($_GET["hashtag"])));
		if (!$pullQuery || $pullQuery == "" || $pullQuery == null) {
			$errorArray = Array(
				"error"=>true,
				"results"=>false,
				"problem"=>"MISSING_TWITTER_SEARCH_KEY"
			);
			echo json_encode($errorArray);
			exit;
		}

		$bearer_token = get_bearer_token();

		$searchData = search_for_a_term($bearer_token, $pullQuery);

		$properties = get_object_vars(json_decode($searchData));

		$tweetArray = Array();

		$parseDataArray = Array(
			"requests"=>Array()
		);

		foreach($properties["statuses"] as $tweet)
		{
			$tweetText = urlencode($tweet->text);
			$alAPIKey = "2094dd01fd7cbceb7e1bb916840e40e81f25d16f";
			$alURL = "http://access.alchemyapi.com/calls/text/TextGetTextSentiment?apikey=".$alAPIKey."&text=".$tweetText."&outputMode=json";
			$al = curl_init();
			curl_setopt($al, CURLOPT_URL, $alURL);
			curl_setopt($al, CURLOPT_RETURNTRANSFER, true);
			$alchemy = curl_exec($al);
			curl_close($al);

			$alData = get_object_vars(json_decode($alchemy));

			if (($alData["docSentiment"]->type != "neutral" && $alData["docSentiment"]->type != null) && $alData["docSentiment"]->score != null) {

				$tempTweetArray = Array(
					"text"=>$tweet->text,
					"data"=>Array(
						"userID"=>$tweet->user->id,
						"userName"=>$tweet->user->screen_name,
						"utc_offset"=>$tweet->user->utc_offset,
						"time_zone"=>$tweet->user->time_zone,
						"profile_image"=>$tweet->user->profile_image_url_https,
						"tweetID"=>$tweet->id,
						"postedAt"=>$tweet->created_at,
						"hashtag"=>$pullQuery
					),
					"sentiment"=>Array(
						"type"=>$alData["docSentiment"]->type,
						"score"=>$alData["docSentiment"]->score
					)
				);
				array_push($tweetArray, $tempTweetArray);

				$parseDataArray["requests"][] = Array(
					"method"=>"POST",
					"path"=>"/1/classes/tweets",
					"body"=>Array(
						"Hashtag"=>$pullQuery,
						"Tweet_id"=>$tweet->id,
						"Tweet_text"=>$tweet->text,
						"Tweet_postedAt"=>$tweet->created_at,
						"Tweet_userID"=>$tweet->user->id,
						"Tweet_username"=>$tweet->user->screen_name,
						"Sentiment_type"=>$alData["docSentiment"]->type,
						"Sentiment_score"=>$alData["docSentiment"]->score
					)
				);
			}
		}

		insertDataWithLargeArray($parseDataArray);

		print_r(json_encode($tweetArray));

		invalidate_bearer_token($bearer_token);
		break;

	case "load":

		$paramQUERY = urldecode(stripslashes(trim($_GET["hashtag"])));
		if ($paramQUERY == "" || $paramQUERY == null) {
			echo "NO_HASHTAG_PARAM";
			exit;
		}

		$data = loadData($paramQUERY, "tweets");

		print_r($data);

		break;

	default: 
		$noRoute = Array(
			"error"=>true,
			"results"=>false,
			"problem"=>"NO_ROUTE_SUPPLIED"
		);
		print_r(json_encode($noRoute));
		break;
}

?>