#!/bin/bash

export HASS_HOME=/volume1/docker/hass
export REPORT_APP=$HASS_HOME/ustb-daily-report/ustb-report
export DIV='=============================================================================='


read_from_cli(){
    echo -e "$DIV"; read -p $'Please paste your USER_NAME here. (please keep username aline with hass user name, e.g., terrance)\n' USER_NAME
    echo -e "$DIV"; read -p $'Please paste your COOKIE here. (e.g., SECKEY_ABVK=....JSESSIONID=1234)\n' COOKIE
    echo -e "$DIV"; read -p $'Please paste your USER_AGENT here. (e.g., Mozilla/5.0....ABI/arm64)\n' USER_AGENT
    echo -e "$DIV"; read -p $'Please paste your DATA here. (e.g., m=yqinfo&....&sfzjwgfxdqqtx=否)\n' DATA
}

create_database(){
    # add content files
     mkdir -p $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj
}

update_database(){
    # put data into files
    echo $COOKIE > $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj/REPORT-COOKIE
    echo $USER_AGENT > $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj/REPORT-UA
    echo $DATA > $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj/REPORT-DATA
}

# ping test
test_user(){
    bash $REPORT_APP test $USER_NAME bj > /dev/null
    if [ $? -ne 0 ]
    then
        echo -e "$DIV\nConfiguration test failed!\n"
        rm -rf $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj
        rm -f $HASS_HOME/ustb-daily-report/log/$USER_NAME.log
        exit -1;
    else
        [ "x$1" == "xadd" ] && echo -e "$DIV\nConfiguration test passed!";
        [ "x$1" == "xupdate" ] && echo -e "$DIV\nUpdate test passed!";
    fi
}

hass_add_user(){
    # add shell commands
    cat $HASS_HOME/template-conf.yaml | sed "s/template/$USER_NAME/g" >> $HASS_HOME/configuration.yaml
    # add report automation
    cat $HASS_HOME/template-auto.yaml | sed "s/template/$USER_NAME/g" >> $HASS_HOME/automations.yaml
    # add keep-alive automation
    sed -i "/- service: shell_command.ustb_ping_yzl/a\  - service: shell_command.ustb_ping_$USER_NAME" $HASS_HOME/automations.yaml
    # restart docker to enable automatations
    echo -e "$DIV\nRestarting HASS to enable new configuration"
    /usr/local/bin/docker restart hass
}


check_existance(){
    if [[ -d $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj ]]
    then
        echo -e "$DIV\nExisting user! Update data by clicking bupdate button."
        exit -1
    fi
}

check_none_existance(){
    if [[ ! -d $HASS_HOME/ustb-daily-report/data/$USER_NAME-bj ]]
    then
        echo -e "$DIV\nUser Not exist, contact admin for a user"
        exit -1
    fi
}

# collect data
if [ "x$1" == "xadd" ]
then
    USER_NAME=$2
    USER_AGENT=$3
    COOKIE=$4
    DATA=$5
    check_existance
    create_database
    update_database
    test_user $1
    hass_add_user
elif [ "x$1" == "xupdate" ]
then
    USER_NAME=$2
    USER_AGENT=$3
    COOKIE=$4
    DATA=$5
    check_none_existance
    update_database
    test_user $1
else
    read_from_cli
    check_existance
    create_database
    update_database
    test_user "add"
    hass_add_user
fi