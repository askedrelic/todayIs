# Manual Build
sudo docker build -t todayis .

sudo docker run --rm=true -it --link grave_wozniak:mysql todayis

Also disable checks, so that this can exist as a cron only service.
dokku checks:disable shack

