### Useful Commands

```bash
CX_Key="6fc113dc074fee638b046c373c91247d" CX_Secret="123456" acme.sh --issue -d phus.lu -d www.phus.lu -d ipv6.phus.lu --dns dns_cx --force --log
```


### DDNS.py

```
./ddns.py cx_ddns --api-key 6fc113dc074fee638b046c373c91247d --api-secret 123456 --domain phus.lu.
```

- for ipv6

```
./ddns.py cx_update --api-key 6fc113dc074fee638b046c373c91247d --api-secret 123456 --domain-id 254151 --host @ --ip $(./ddns.py getip --iface teredo)
```
