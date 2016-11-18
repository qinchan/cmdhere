#!/usr/bin/env python
# coding:utf-8

import sys
import os
import getopt
import json
import hashlib
import email.utils
import urllib2
import logging

logging.basicConfig(format='%(levelname)s:%(message)s', level=logging.INFO)


def getip(iface):
    ip = ''
    lines = os.popen('ip addr show {}'.format(iface)).read().splitlines()
    for line in lines:
        line = line.strip()
        if line.startswith(('inet ', 'inet6 ')):
            ip = line.split()[1].split('/')[0]
            break
    return ip


def cx_ddns(api_key, api_secret, domain, ip=''):
    api_url = 'https://www.cloudxns.net/api2/ddns'
    data = json.dumps({'domain': domain, 'ip': ip, 'line_id': '1'})
    date = email.utils.formatdate()
    api_hmac = hashlib.md5(''.join((api_key, api_url, data, date, api_secret))).hexdigest()
    headers = {'API-KEY': api_key, 'API-REQUEST-DATE': date, 'API-HMAC': api_hmac, 'API-FORMAT': 'json'}
    resp = urllib2.urlopen(urllib2.Request(api_url, data=data, headers=headers))
    logging.info('cx_ddns domain=%r to ip=%r result: %s', domain, ip, resp.read())


def cx_update(api_key, api_secret, domain_id, host, ip):
    api_url = 'https://www.cloudxns.net/api2/record/{}'.format(domain_id)
    date = email.utils.formatdate()
    api_hmac = hashlib.md5(''.join((api_key, api_url, date, api_secret))).hexdigest()
    headers = {'API-KEY': api_key, 'API-REQUEST-DATE': date, 'API-HMAC': api_hmac, 'API-FORMAT': 'json'}
    resp = urllib2.urlopen(urllib2.Request(api_url, data=None, headers=headers))
    data = json.loads(resp.read())['data']
    record_id = int((x['record_id'] for x in data if x['type']==('AAAA' if ':' in ip else 'A') and x['host']==host).next())
    logging.info('cx_update query domain_id=%r host=%r to record_id: %r', domain_id, host, record_id)
    api_url = 'https://www.cloudxns.net/api2/record/{}'.format(record_id)
    data = json.dumps({'domain_id': domain_id, 'host': host, 'value': ip})
    date = email.utils.formatdate()
    api_hmac = hashlib.md5(''.join((api_key, api_url, data, date, api_secret))).hexdigest()
    headers = {'API-KEY': api_key, 'API-REQUEST-DATE': date, 'API-HMAC': api_hmac, 'API-FORMAT': 'json'}
    request = urllib2.Request(api_url, data=data, headers=headers)
    request.get_method = lambda: 'PUT'
    resp = urllib2.urlopen(request)
    logging.info('cx_update update domain_id=%r host=%r ip=%r result: %r', domain_id, host, ip, resp.read())
    return


def main():
    funcs = sorted([v for v in globals().values() if type(v) is type(main) and v is not main])
    if len(sys.argv) == 1:
        params = {f.func_name:f.func_code.co_varnames[:f.func_code.co_argcount] for f in funcs}
        print 'Usage: {0} command [arguments]\n\nExamples:\n'.format(sys.argv[0]),
        print '\n'.join('\t{0} {1} {2}'.format(sys.argv[0], k, ' '.join('--{0} {1}'.format(x.replace('_', '-'), x.upper()) for x in v)) for k, v in params.items())
        return
    command = sys.argv[1]
    arguments = sys.argv[2:]
    for f in funcs:
        if f.func_name == command:
            options = [x.replace('_','-')+'=' for x in f.func_code.co_varnames[:f.func_code.co_argcount]]
            kwargs, _ =  getopt.gnu_getopt(arguments, '', options)
            kwargs = {k[2:].replace('-', '_'):v for k, v in kwargs}
            logging.info('main %s(%s)', f.func_name, kwargs)
            result = f(**kwargs)
            if result:
                print result
            return


if __name__ == '__main__':
    main()
