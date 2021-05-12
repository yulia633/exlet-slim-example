start:
	php -S localhost:8080 -t public public/index.php
	
deb:
   XDEBUG_MODE=debug make start