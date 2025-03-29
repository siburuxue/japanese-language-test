#!/bin/bash

# shellcheck disable=SC2006
. function.sh

# ./clear.sh -t test_dict
table=""
while getopts ":t:h" optname
do
  case "$optname" in
    "t")
      table="$OPTARG"
      ;;
    "h")
      echo "./clear.sh -t test_dict"
      echo ""
      echo "Usage: clear [OPTIONS]"
      echo "    -t       the curd's table name"
      exit
      ;;
    *)
      echo "unknow options $optname "
      ;;
  esac
done

if [ "$table" == "" ];
then
  echo "-t can not be empty(table name)"
  exit 1
fi

dir=${table/_/\/}
route=${table/_/-}
entity=`getEntityName "${table}" "_"`

rm -rf src/Controller/"${entity}"Controller.php
rm -rf src/Service/"${entity}"Service.php
rm -rf templates/admin/"${dir}"/*
rm -rf public/templates/"${table}".xlsx

git checkout -f config/routes/admin/routes.yaml
git checkout -f src/Lib/Constant/Route.php
git checkout -f src/Repository/"${entity}"Repository.php

php bin/console clear:curd --prefix="$route"