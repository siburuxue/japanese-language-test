#!/bin/bash

# ./curd.sh -t test_dict -n 测试字典 -i "fa fa-flask" -e true -r true

# shellcheck disable=SC2006
. function.sh

table=""
title=""
group=""
icon=""
export=""
readonly=""
import=""
while getopts ":t:n:g:i:e:r:u:h" optname
do
  case "$optname" in
    "t")
      table="$OPTARG"
      ;;
    "n")
      title="$OPTARG"
      ;;
    "g")
      group="$OPTARG"
      ;;
    "i")
      icon="$OPTARG"
      ;;
    "e")
      export="--export"
      ;;
    "r")
      readonly="--readonly"
      ;;
    "u")
      import="--import"
      ;;
    "h")
      echo "./curd.sh -t test_dict -n 测试字典 -g '测试' -i 'fa fa-flask'"
      echo "./curd.sh -t test_dict -n 测试字典 -i 'fa fa-flask' -e true -u true"
      echo "./curd.sh -t test_dict -n 测试字典 -i 'fa fa-flask' -e true -r true"
      echo ""
      echo "Usage: curd [OPTIONS]"
      echo "    -t     table name(--table)"
      echo "    -n     title name(--title) "
      echo "    -g     group name(--group)"
      echo "    -i     icon name(--icon)"
      echo "    -e     add export function(--export)"
      echo "    -u     add import function(--import)"
      echo "    -r     only search function(--readonly)"
      exit
      ;;
    *)
      echo "unknow options $optname "
      ;;
  esac
done

if [ "$table" == "" ];
then
  echo "-t can not be empty(--table)"
  exit 1
fi

if [ "$title" == "" ];
then
  echo "-n can not be empty(--title)"
  exit 1
fi

status=`git status|grep "nothing to commit, working tree clean"`
if [ "$status" == "" ];then
  echo "create curd 之前请先提交代码。"
  exit 1;
fi

entity=`getEntityName ${table} "_"`
command="php bin/console create:curd --title=${title} --table=${table} --service=${entity} --controller=${entity} --extra=${table}.json --group='${group}' --icon='${icon}'"
command="$command"" $export"" $import"" $readonly"
eval $command
#php bin/console create:curd --title=测试字典 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon="fa fa-flask" --export
#php bin/console create:curd --title=测试字典 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon="fa fa-flask" --export
#php bin/console create:curd --title=测试字典 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon="fa fa-flask" --readonly
#php bin/console create:curd --title=测试字典 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json  --group="" --icon="fa fa-flask" --readonly --export