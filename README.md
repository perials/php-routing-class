# A PHP class for MVC routing

## Installation
```php
require 'route.php';
$route = new Route();
```

## .htaccess
Usually in MVC pattern all the requests are handled by a index.php file. We have provided a sample .htaccess file that redirects all requests to index.php file. Below is the code in the .htaccess file
```
Options -MultiViews
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```
What the above code does is checks whether the requested url is pointing to file or a directory. If it is not then it forward the request to index.php.

## Add routing rule
In your index.php (or any other bootstrap file) you will have to add the routing rules
```php
//Handling route request in closure
$route->add(['GET', '/', function(){ echo "This is home page"; }]);

$route->add(['GET', 'about-us', 'PagesController@about']);

$route->add(['GET', 'news/{id}', 'NewsController@view']);

$route->add(['POST', 'archive/{post_type}/yearly/{year}', 'ArchiveController@yearly']);
```

## Dispatch
Now tell the route class to match the current request against the added rules and return found match if any.
```php
$controller_method_param_array = $route->dispatch();
```

dispatch method will return
 - an empty array if no match found.
 - an array if match found.

If routing rule uses a closure to handle this particular request (for example '/' in above examples) then it will return
```php
[
    'is_closure'=>true,
    'closure'=>Closure Object()
]
```
Else (consider 'archive/news/yearly/2015') it will return below array
```php
[
    'is_closure'=>false,
    'controller'=>'NewsController',
    'method'=>'view',
    'params'=>['news','2015']
]
```

## Handling result returned by dispatch
The dispatch function only returns an array and doesn't attempt to call any controller. This allows you to have a better control of your application. Still below is a sample code of how typically this is handled.
```php
if( empty( $controller_method_param_array ) ) {
    echo "No matching rule found";
}
elseif( $controller_method_param_array['is_closure'] ) {
    $controller_method_param_array['closure']();
}
else {
    $response = call_user_func_array(
                    array(
                        new $controller_method_param_array['controller'],
                        $controller_method_param_array['method']
                    ),
                    $controller_method_param_array['controller']['params']
                    );
}
```