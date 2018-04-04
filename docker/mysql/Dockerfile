FROM mysql:5.7

#RUN apt-get update \
#  && apt-get install --no-install-recommends -y \
#     vim \
#     netcat-openbsd

ADD /docker/mysql/dev.cnf /etc/mysql/conf.d/dev.cnf
ADD /docker/mysql/remote-access.cnf /etc/mysql/conf.d/remote-access.cnf
ADD /docker/mysql/performance-schema.cnf /etc/mysql/conf.d/performance-schema.cnf
COPY /docker/mysql/grant.sql /docker-entrypoint-initdb.d/grant.sql
#COPY /docker/mysql/createuser.sh /tmp/createuser.sh
#RUN chmod +rwx /tmp/createuser.sh
#RUN /tmp/createuser.sh

