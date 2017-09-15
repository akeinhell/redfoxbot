docker run -d -p 80:80 -p 443:443 \
	--network main-network \
	--name nginx \
	-v /var/run/docker.sock:/tmp/docker.sock:ro \
	-v ~/docker/nginx/certs:/etc/nginx/certs \
	-v ~/docker/nginx/vhost.d:/etc/nginx/vhost.d \
	--restart always \
	jwilder/nginx-proxy
