FROM ubuntu:14.04
MAINTAINER Matt Behrens <askedrelic@gmail.com>

# Install useful programs, the right way
RUN     apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
            curl \
            git \
            vim \
            software-properties-common \
            php5 \
            php5-mysql \
            php5-curl \
            mysql-client \
            python \
        && apt-get clean

# add all required files in /data/
ADD . /data/

# ADD ./CHECKS /app/CHECKS
# CMD cd /media && python -m SimpleHTTPServer 9000
# EXPOSE 9000
