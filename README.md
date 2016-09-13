### Useful Commands

```bash
openssl ecparam -genkey -name prime256v1 > ecc.key
openssl req -new -sha256 -key ecc.key -subj "/CN=vps.phus.lu" -reqexts SAN -config <(cat /etc/ssl/openssl.cnf <(printf "[SAN]\nsubjectAltName=DNS:vps.phus.lu")) -outform der -out ecc.csr
sudo letsencrypt certonly --text -n -vv --agree-tos --email phuslu@hotmail.com --csr ecc.csr --webroot -w /var/www/html
```
