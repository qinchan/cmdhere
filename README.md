### Useful Commands

```bash
DOMAIN=vps.phus.lu
openssl ecparam -genkey -name prime256v1 > ecc.key
openssl req -new -sha256 -key ecc.key -subj "/CN=$DOMAIN" -reqexts SAN -config <(cat /etc/ssl/openssl.cnf /etc/pki/tls/openssl.cnf 2>/dev/null <(printf "[SAN]\nsubjectAltName=DNS:$DOMAIN")) -outform der -out ecc.csr
sudo letsencrypt certonly --text -n -vv --agree-tos --email admin@example.com --csr ecc.csr --webroot -w /var/www/html
```
