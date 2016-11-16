#!/bin/bash -ex

CX_Key=${CX_Key:-6fc113dc074fee638b046c373c91247d}
CX_Secret=${CX_Secret:-123456}

function ddns() {
	local url="https://www.cloudxns.net/api2/ddns"
	local domain=$1
	local iface=$2

	if  [ -n "$ip" ] ; then
		local ip=$(ip -4 addr show $iface | awk '/ *(inet6|inet)/ {gsub(/\/.+$/, "", $2); print $2; exit}')
	else
		local ip=""
	fi

	local data="{\"domain\":\"${domain}\",\"ip\":\"${ip}\",\"line_id\":\"1\"}"
	local time=$(date -R)
	local hmac=$(echo -n "${CX_Key}${url}${data}${time}${CX_Secret}" | md5sum | awk '{print $1}')
	local header1="API-KEY: ${CX_Key}"
	local header2="API-REQUEST-DATE: ${time}"
	local header3="API-HMAC: ${hmac}"
	local header4="API-FORMAT: json"

	local result=$(curl -m 16 -k -X POST -H "${header1}" -H "${header2}" -H "${header3}" -H "${header4}" -d "$data" ${url})
	logger -t ddns "update ddns result: ${result} for domain: ${domain} iface: ${iface}"
}

function ddns6() {
	local domain_id=$1
	local host=$2
	local iface=$3

	local url="https://www.cloudxns.net/api2/record/${domain_id}"
	local time=$(date -R)
	local hmac=$(echo -n "${CX_Key}${url}${time}${CX_Secret}" | md5sum | awk '{print $1}')
	local header1="API-KEY: ${CX_Key}"
	local header2="API-REQUEST-DATE: ${time}"
	local header3="API-HMAC: ${hmac}"
	local header4="API-FORMAT: json"

	local record_id=$(curl -m 16 -v -k -H "${header1}" -H "${header2}" -H "${header3}" -H "${header4}" ${url} | python -c 'import sys, json; print (x["record_id"] for x in json.loads(sys.stdin.read())["data"] if x["type"]=="AAAA" and x["host"]==sys.argv[1]).next()' "${host}")
	logger -t ddns  "query record_id: ${record_id} for domain_id: ${domain_id} host: ${host}"

	local ip=$(ip -6 addr show $iface | awk '/ *(inet6|inet)/ {gsub(/\/.+$/, "", $2); print $2; exit}')
	logger -t ddns "enumerate ip: ${ip} for domain_id: ${domain_id} host: ${host} record_id: ${record_id}"

	local url="https://www.cloudxns.net/api2/record/${record_id}"
	local data="{\"domain_id\":\"${domain_id}\",\"host\":\"${host}\",\"value\":\"${ip}\"}"
	local time=$(date -R)
	local hmac=$(echo -n "${CX_Key}${url}${data}${time}${CX_Secret}" | md5sum | awk '{print $1}')
	local header1="API-KEY: ${CX_Key}"
	local header2="API-REQUEST-DATE: ${time}"
	local header3="API-HMAC: ${hmac}"
	local header4="API-FORMAT: json"

	local result=$(curl -m 16 -v -k -X PUT -H "${header1}" -H "${header2}" -H "${header3}" -H "${header4}" -d "${data}" ${url})
	logger -t ddns "update record result: ${result} for domain_id: ${domain_id} host: ${host}"
}

ddns "phus.lu." ""
ddns6 "254151" "@" "teredo"


