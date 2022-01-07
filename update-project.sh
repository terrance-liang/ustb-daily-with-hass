#!/bin/bash
rsync -avz \
    /volume1/docker/hass/ustb-daily-report/ustb.php \
    /volume1/mount/home/www/

git status
read -p "Git Commit: " cmt
git add . && git commit -m "$cmt" && git push