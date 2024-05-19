FROM mysql:8.0

ENV MYSQL_DATABASE=task-manager-db
ENV MYSQL_USERNAME=laravel
ENV MYSQL_PASSWORD=password
ENV MYSQL_ROOT_PASSWORD=password

RUN mkdir -p /etc/mysql/mariadb.conf.d && \
echo "[mysqld]\n\
user=mysql\n\
bind-address=0.0.0.0\n\
\n\
# Configure for Docker networking\n\
skip-networking=True" > /etc/mysql/mariadb.conf.d/docker.cnf

# Expose port 3306 to allow access to MariaDB
EXPOSE 3306

# Define the default command to run MariaDB server
CMD ["mysqld"]