FROM trafex/alpine-nginx-php7:latest
COPY --chown=nobody src/ /var/www/html/
COPY config/nginx-server.conf /etc/nginx/conf.d/server.conf
VOLUME ["/var/www/html/cover"]
