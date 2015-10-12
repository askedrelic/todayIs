FROM ubuntu:14.04
MAINTAINER Matt Behrens <askedrelic@gmail.com>

# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get -y upgrade

# nice to have programs
RUN apt-get -y install curl git vim
# required to run the script
RUN apt-get -y install php5 php5-mysql php5-curl mysql-client

# add all required files in /data/
ADD . /data/

EXPOSE 9000
WORKDIR /media/
CMD python -m SimpleHTTPServer 9000
