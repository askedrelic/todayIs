# TodayIs

dokku config to setup
```
# disable checks, for cron only service
dokku checks:disable shack
# setup vars
dokku config:set shack USE DOKKU_SKIP_DEPLOY=true SHACKUSER=user SHACKPASS=pass
```

hi
