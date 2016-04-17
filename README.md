# Manual Build
sudo docker build -t todayis .

sudo docker run --rm=true -it --link grave_wozniak:mysql todayis
