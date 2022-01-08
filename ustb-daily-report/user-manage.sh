#!/bin/bash

export HASS_HOME=/volume1/docker/hass
export SCRIPT_HOME=$HASS_HOME/ustb-daily-report/
export REPORT_APP=$HASS_HOME/ustb-daily-report/ustb-report
export DIV='=============================================================================='
export WWW_DIR="/volume1/mount/home/www/ustb-log"
export LOG_FILE="$SCRIPT_HOME/manage.log"
export KEEP_ALIVE_LINE=8
# recevie data from CLI
INPUT_CMD=$1
USER_NAME=$2
USER_AGENT=$3
COOKIE=$4
DATA=$5
USER_LOC=$6
USER_LATI=$7
USER_LONG=$8

# functions

write_log() {
        printf "[%s] %s\n" "$(date +"%Y-%m-%d %H:%M:%S")" "$1" | tee -a $LOG_FILE
        rsync -az $LOG_FILE $WWW_DIR/ >/dev/null 2>&1
        return 0
}

read_from_cli(){
    echo -e "$DIV"; read -p $'Please paste your USER_NAME here. (please keep username aline with hass user name, e.g., terrance)\n' USER_NAME
    echo -e "$DIV"; read -p $'Please paste your COOKIE here. (e.g., SECKEY_ABVK=....JSESSIONID=1234)\n' COOKIE
    echo -e "$DIV"; read -p $'Please paste your USER_AGENT here. (e.g., Mozilla/5.0....ABI/arm64)\n' USER_AGENT
    echo -e "$DIV"; read -p $'Please paste your DATA here. (e.g., m=yqinfo&....&sfzjwgfxdqqtx=否)\n' DATA
}

create_database(){
    # add content files
    mkdir -p $HASS_HOME/ustb-daily-report/data/$USER_NAME/$USER_LOC
    mkdir -p $HASS_HOME/ustb-daily-report/data/$USER_NAME/log
}

write_into_database(){
    create_database
    # put data into files
    echo $COOKIE > $HASS_HOME/ustb-daily-report/data/$USER_NAME/$USER_LOC/REPORT-COOKIE
    echo $USER_AGENT > $HASS_HOME/ustb-daily-report/data/$USER_NAME/$USER_LOC/REPORT-UA
    echo $DATA > $HASS_HOME/ustb-daily-report/data/$USER_NAME/$USER_LOC/REPORT-DATA
}

test_data(){
    bash $REPORT_APP test $USER_NAME $USER_LOC > /dev/null
    if [ $? -ne 0 ]
    then
        write_log "Configuration test failed!"
        rm -rf $HASS_HOME/ustb-daily-report/data/$USER_NAME/$USER_LOC
        rm -f $HASS_HOME/ustb-daily-report/log/$USER_NAME.log
        exit -1
    else
        write_log "Configuration test passed!"
    fi
}

hass_check_location_existance(){
    grep "\  - name: $USER_LOC" $HASS_HOME/configuration.yaml >/dev/null 2>&1 && return 1
    return 0
}

hass_add_location(){
    write_log "Adding new zone $USER_LOC into hass."
    sed -i "/^zone:/r $HASS_HOME/template-zone.yaml" $HASS_HOME/configuration.yaml
    sed -i "s/template-loc/$USER_LOC/g" $HASS_HOME/configuration.yaml
    sed -i "s/template-lati/$USER_LATI/g" $HASS_HOME/configuration.yaml
    sed -i "s/template-long/$USER_LONG/g" $HASS_HOME/configuration.yaml
}

hass_restart(){
    # restart docker to enable automatations
    write_log "Restarting HASS to enable new configuration."
    /usr/local/bin/docker restart hass >/dev/null
}

hass_check_ping_exist(){
    grep "shell_command.ustb_ping_$USER_NAME" $HASS_HOME/automations.yaml >/dev/null 2>&1
}

hass_auto_add_keepalive(){
    hass_check_ping_exist && return 0
    sed -i "$KEEP_ALIVE_LINE i\  - service: shell_command.ustb_ping_$USER_NAME" $HASS_HOME/automations.yaml
}

hass_auto_add_report(){
    sed -i "\$r $HASS_HOME/template-auto.yaml" $HASS_HOME/automations.yaml
    sed -i "s/template-name/$USER_NAME/g" $HASS_HOME/automations.yaml
    sed -i "s/template-loc/$USER_LOC/g" $HASS_HOME/automations.yaml
}

hass_auto_update_keepalive(){
    # remove exsistant ping function
    sed -i "/ustb_ping_$USER_NAME/d" $HASS_HOME/configuration.yaml
    # add shell commands
    sed -i "\$r $HASS_HOME/template-conf.yaml" $HASS_HOME/configuration.yaml
    sed -i "/ustb_report_template-name_template-loc/d" $HASS_HOME/configuration.yaml
    sed -i "s/template-name/$USER_NAME/g" $HASS_HOME/configuration.yaml
    sed -i "s/template-loc/$USER_LOC/g" $HASS_HOME/configuration.yaml
}

hass_conf_add_ping_report(){
    sed -i "/ustb_ping_$USER_NAME/d" $HASS_HOME/configuration.yaml
    sed -i "\$r $HASS_HOME/template-conf.yaml" $HASS_HOME/configuration.yaml
    sed -i "s/template-name/$USER_NAME/g" $HASS_HOME/configuration.yaml
    sed -i "s/template-loc/$USER_LOC/g" $HASS_HOME/configuration.yaml
}

hass_auto_add_ping_report(){
    hass_auto_add_report
    hass_auto_add_keepalive
}

hass_add_and_switch_to_new_loc(){
    hass_conf_add_ping_report
    hass_auto_add_ping_report
}

hass_switch_to_prev_loc(){
    hass_auto_update_keepalive
}

check_user_existance(){
    grep -Rn "\"username\": \"$USER_NAME\"" $HASS_HOME/.storage/ >/dev/null && return 1
    write_log "User Not exist, contact admin for a user." && exit -1
}

check_user_loc_data_existance(){
    test -d $HASS_HOME/ustb-daily-report/data/$USER_NAME/$USER_LOC
}

check_user_current_loc(){
    grep "ustb_ping_$USER_NAME" $HASS_HOME/configuration.yaml | awk '{print $9}' >/dev/null
}

user_switch_loc(){
    check_user_current_loc
    [[ "x$?" != "x$USER_LOC" ]] && hass_switch_to_prev_loc
}

user_data_write_and_test(){
    write_into_database
    test_data
}

case $INPUT_CMD in
submit) #add or update
    # check user / location info, failing leads to exit
    check_user_existance  
    # write into database and test user
    
    # if location does net exist, create one
    hass_check_location_existance
    [[ $? -eq 0 ]] && hass_add_location
    
    # if the input user-loc pair exists && does net match the current location
    # switch to the prev location
    check_user_loc_data_existance && user_switch_loc && user_data_write_and_test && hass_restart && exit 0
    
    # otherwise add conf & auto, ping & report
    hass_add_and_switch_to_new_loc && user_data_write_and_test && hass_restart && exit 0
;;
remove)
    # backup
    cp -rf $HASS_HOME/ustb-daily-report/data/ $HASS_HOME/ustb-daily-report/data.removed/
    cp $HASS_HOME/automations.yaml $HASS_HOME/automations.yaml.removed
    cp $HASS_HOME/configuration.yaml $HASS_HOME/configuration.yaml.removed
    # remove contents
    [[ "x$USER_NAME" != "x" ]] && echo "rm -rf $HASS_HOME/ustb-daily-report/data/$USER_NAME"
    sed -i "/alias: ustb-daily-report-$USER_NAME/,+11d" $HASS_HOME/automations.yaml
    sed -i "/shell_command.ustb_ping_$USER_NAME/d" $HASS_HOME/automations.yaml
    sed -i "/ustb_ping_$USER_NAME/d" $HASS_HOME/configuration.yaml
    sed -i "/ustb_report_$USER_NAME/d" $HASS_HOME/configuration.yaml
    hass_restart
;;
test)
    hass_add_location
;;
*)
    read_from_cli
    check_user_loc_data_existance
    write_into_database
    test_data
    hass_auto_add_ping_report
esac