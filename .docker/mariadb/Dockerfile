FROM mariadb


COPY ./.docker/mariadb/init.sql /docker-entrypoint-initdb.d/
COPY ./.docker/mariadb/docker-entrypoint.sh /docker-entrypoint.sh

ENV MYSQL_ROOT_PASSWORD=toor
ENV MYSQL_DATABASE=tdi
ENV MYSQL_USER=tdi
ENV MYSQL_PASSWORD=tdi

RUN chmod +x docker-entrypoint.sh


CMD ["/docker-entrypoint.sh"]