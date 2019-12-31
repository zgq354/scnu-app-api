FROM trafex/alpine-nginx-php7:latest
COPY --chown=nobody . /var/www/html/
COPY config/nginx-server.conf /etc/nginx/conf.d/server.conf
# VOLUME ["/var/www/html/public/cover"]
