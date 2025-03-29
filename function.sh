#!/bin/bash

# shellcheck disable=SC2034
# shellcheck disable=SC2021
# shellcheck disable=SC2068
# shellcheck disable=SC2006

toUpperFirst(){
  local str=$1
  local firstCharacter=${str:0:1}
  local otherCharacters=${str:1}
  firstCharacter=$(echo "$firstCharacter"|tr '[a-z]' '[A-Z]')
  echo "$firstCharacter""$otherCharacters"
}

# split test-dict '-' => (test, dict)
split(){
  local str=$1
  local char=$2
  OLD_IFS="$IFS"
  IFS="${char}"
  local arr=(${str})
  IFS="$OLD_IFS"
  echo ${arr[@]}
}

# getEntityName test-dict "-" => TestDict
# getEntityName test_dict "_" => TestDict
getEntityName(){
  local name=$1
  local char=$2
  nameArray=`split ${name} ${char}`
  local letter=""
  local result=""
  for s in ${nameArray[@]}
  do
    letter=`toUpperFirst "${s}"`
    result="$result""$letter"
  done
  echo "$result"
}