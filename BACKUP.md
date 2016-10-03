# how to backup the data in this app

dokku mariadb:export shackdb > shackdb_$(date +%Y%m%d-%H%M%S).db
