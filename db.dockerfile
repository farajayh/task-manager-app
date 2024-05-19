FROM mysql:8.0

ENV MYSQL_DATABASE=task-manager-db
ENV MYSQL_USERNAME=laravel
ENV MYSQL_PASSWORD=password
ENV MYSQL_ROOT_PASSWORD=password

COPY create_database.sql /docker-entrypoint-initdb.d/

RUN echo "[mysqld]
user=mysql
bind-address=0.0.0.0

# Configure for Docker networking
skip-networking=True" > /etc/mysql/mariadb.conf.d/docker.cnf