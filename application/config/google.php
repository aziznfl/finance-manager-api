<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Google API Configuration
| -------------------------------------------------------------------
| 
| To get API details you have to create a Google Project
| at Google API Console (https://console.developers.google.com)
| 
|  client_id         string   Your Google API Client ID.
|  client_secret     string   Your Google API Client secret.
|  redirect_uri      string   URL to redirect back to after login.
|  application_name  string   Your Google application name.
|  api_key           string   Developer key.
|  scopes            string   Specify scopes
*/
$config['google']['client_id']        = '693599868927-sl0s3kk1lrsvb9udromokn7p7r7firht.apps.googleusercontent.com';
$config['google']['client_secret']    = '9aUCNUk-RWPT-CUkDu54I7y0';
$config['google']['redirect_uri']     = 'http://fm.aziznfl.com/account/logingoogle';
$config['google']['application_name'] = 'Finance Manager';
$config['google']['api_key']          = 'AIzaSyDzgbNeW8DbhSEm6XKpOySeyNi0nzhNwXA';
$config['google']['scopes']           = array();