<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "player";
$route['404_override'] = 'errors/page_missing';

$route['ajax/(:any)'] = 'ajax/$1';
$route['api/(:any)'] = 'api/$1';
$route['artist/(:any)'] = 'artist/$1';
$route['artistajax/(:any)'] = 'artistajax/$1';
$route['dev/(:any)'] = 'dev/$1';
$route['email/(:any)'] = 'email/$1';
$route['errors/(:any)'] = 'errors/$1';
$route['mobile/(:any)'] = 'mobile/$1';
$route['out/(:any)'] = 'out/$1';
$route['labs/(:any)'] = 'labs/$1';
$route['player/(:any)'] = 'player/$1';
$route['test/(:any)'] = 'test/$1';

$route['ajax'] = 'ajax';
$route['api'] = 'api';
$route['artist'] = 'artist';
$route['artistajax'] = 'artistajax';
$route['dev'] = 'dev';
$route['email'] = 'email';
$route['errors'] = 'errors';
$route['mobile'] = 'mobile';
$route['out'] = 'out';
$route['player'] = 'player';
$route['test'] = 'test';

$route['(:any)'] = 'player/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
