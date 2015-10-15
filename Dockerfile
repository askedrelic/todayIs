FROM ubuntu:14.04
MAINTAINER Matt Behrens <askedrelic@gmail.com>

# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get -y update

# nice to have programs
RUN apt-get -y install curl git vim
# required to run the script
RUN apt-get -y update && apt-get -y install php5 php5-mysql php5-curl mysql-client python

# add all required files in /data/
ADD . /data/

ADD ./CHECKS /app/CHECKS
EXPOSE 9000
CMD cd /media && python -m SimpleHTTPServer 9000
