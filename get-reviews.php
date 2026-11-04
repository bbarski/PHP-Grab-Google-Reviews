<?php

$google_api_options = array(
  'googlemaps_api_url_by_place_id' => 'https://maps.googleapis.com/maps/api/place/details/json?place_id=', // Googlemaps api url by place id
  'googlemaps_free_apikey' => '&key=',       // Google API Key
  'google_maps_review_cid' => 'ChIJHegKoJUfyUwRjMxaCcviZDA', // Google Placec ID of the Business
  'google_reviews_sorting' => '&reviews_sort=most_relevant',  // reviews are sorted by relevance (default), or in chronological order (most_relevant/newest)
  'cache_data_xdays_local' => 30,       // every x day the reviews are loaded from google (save API traffic)
  'your_language_for_tran' => 'en',     // give you language for auto translate reviews
  'show_not_more_than_max' => 5,        // (0-5) only show first x reviews
  'show_only_if_with_text' => false,    // true = show only reviews that have text
  'show_only_if_greater_x' => 0,        // (0-4) only show reviews with more than x stars
  'sort_reviews_by_a_data' => 'rating', // sort by 'time' or by 'rating' (newest/best first)
  'show_cname_as_headline' => true,     // true = show customer name as headline
  'show_stars_in_headline' => true,     // true = show customer stars after name in headline
  'show_author_avatar_img' => true,     // true = show the author avatar image (rounded)
  'show_blank_star_till_5' => true,     // false = don't show always 5 stars e.g. ⭐⭐⭐☆☆
  'show_txt_of_the_review' => true,     // true = show the text of each review
  'show_author_of_reviews' => true,     // true = show the author of each review
  'show_age_of_the_review' => true,     // true = show the age of each review
  'dateformat_for_the_age' => 'Y.m.d',  // see https://www.php.net/manual/en/datetime.format.php
  'show_rule_after_review' => true,     // false = don't show <hr> Tag after each review (and before first)
  'add_schemaorg_metadata' => true,     // add schemo.org data to loop back your rating to SERP
);

$curl_options = array(
  'http_header_accept_language' => 'en', // Curl request http accept language
  'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
  'ssl_verifypeer' => 'false',
  'return_transfer' => 'true'
);

function isReviewFilePresent() {
  return file_exists('reviews.json');
}

function isReviewFileLatest() {
  if (filemtime('reviews.json') > strtotime('-'.$options['cache_data_xdays_local'].' days'))
  {return true;} else {return false;}
}



function isCurlEnabled() {
  return function_exists('curl_version');
}

function setCurlUrl() {
  return   $google_api_options['googlemaps_api_url_by_place_id']
          .$google_api_options['google_maps_review_cid']
          .$google_api_options['google_reviews_sorting']
          .$google_api_options['googlemaps_free_apikey'];
}

function initCurlRequest() {
  $curl_request = curl_init();
  
  curl_setopt($curl_request, CURLOPT_URL, setCurlUrl());
  curl_setopt($curl_request, CURLOPT_HTTPHEADER, array('Accept-Language: '.$curl_options['http_header_accept_language']));
  curl_setopt($curl_request, CURLOPT_USERAGENT, $curl_options['user_agent']);
  curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, $curl_options['ssl_verifypeer']);
  curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, $curl_options['return_transfer']);
  
  return $curl_request;

}

function getReviews($options) {
  if (isReviewFilePresent() && isReviewFileLatest())
      { return file_get_contents('reviews.json'); }
  else 
      { 
        if (isCurlEnabled())
            { 
              $curl_request = initCurlRequest();
              $json_response = curl_exec($curl_request);
              curl_close($curl_request);
              return $json_response;
            }
        else
            {
              $arrContextOptions=array(
              'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            ),
              'http' => array(
                            'method' => 'GET',
                            'header' => 'Accept-language: '.'en'."\r\n" .
                            "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36\r\n"
                            ) 
              );

              return file_get_contents($url, false, stream_context_create($arrContextOptions));

            }
      }
}

function main(){
  echo $result = getReviews(); 
}

