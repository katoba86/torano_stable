# Torano

Tor Anonymous Object Interface

# CLI-Configuration
All calls are made via the "main.php" 
You start the program via 

    php main.php tor:start 5
    //or how many servers are still needed for the initial start

## tor:status 
shows the status and latency of the last connections. Possible error messages not automatically corrected are also displayed.
VPN Connections are displayed by country

That's it for the CLI. The script runs as a demon in the background.

**Dependencies:**

 - jq
 - tor  
 - redis-server 
 - libcurl 
 - libssl-dev 
 - php-curl 
 - composer

## Web-Interface
Configure nginx-virtual host

    server {
	listen 80;
	root /your/path/to/torano;


	index index.php;
	
	server_name _ localhost;

	location / {
   try_files $uri $uri/ /index.php?$query_string;
	}

	
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
	}
}

Open browser and call IP
http://127.0.0.1

You see: Not allowed without body... 
But: All good!

Torano expects a configuration as JSON body in the request. 
Syntax is the following:

{
   "fetch":{
      "type":"GET",
      "url":"your-url",
      "headers":{
	"Accept":"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
		"Accept-Encoding":"br",
		"Accept-Language":"de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7",
    	"Sec-Fetch-Mode": "navigate",
    	"Sec-Fetch-User": "?1",
    	"User-Agent":"Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Mobile Safari/537.36"
      }
   }
}

