#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./scripts/configure_postfix_relay.sh smtp-relay.example.com 587 username password STARTTLS
#   ./scripts/configure_postfix_relay.sh smtp-relay.example.com 465 username password SSL

if [ "${#}" -ne 5 ]; then
  echo "Usage: $0 <host> <port> <username> <password> <STARTTLS|SSL>"
  exit 1
fi

HOST="$1"
PORT="$2"
USERNAME="$3"
PASSWORD="$4"
TLS_MODE="$(echo "$5" | tr '[:lower:]' '[:upper:]')"

if [ "${TLS_MODE}" != "STARTTLS" ] && [ "${TLS_MODE}" != "SSL" ]; then
  echo "TLS mode must be STARTTLS or SSL"
  exit 1
fi

if ! [[ "${PORT}" =~ ^[0-9]+$ ]]; then
  echo "Port must be numeric"
  exit 1
fi

RELAY_HOST="[${HOST}]:${PORT}"
SASL_FILE="/etc/postfix/sasl_passwd"

echo "Configuring Postfix relayhost ${RELAY_HOST} ..."
echo "${RELAY_HOST} ${USERNAME}:${PASSWORD}" | sudo tee "${SASL_FILE}" >/dev/null
sudo chmod 600 "${SASL_FILE}"
sudo postmap "${SASL_FILE}"
sudo chmod 600 "${SASL_FILE}.db"

sudo postconf -e "relayhost = ${RELAY_HOST}"
sudo postconf -e "smtp_sasl_auth_enable = yes"
sudo postconf -e "smtp_sasl_password_maps = hash:${SASL_FILE}"
sudo postconf -e "smtp_sasl_security_options = noanonymous"
sudo postconf -e "smtp_sasl_tls_security_options = noanonymous"
sudo postconf -e "smtp_tls_CAfile = /etc/ssl/certs/ca-certificates.crt"
sudo postconf -e "smtp_use_tls = yes"

if [ "${TLS_MODE}" = "SSL" ]; then
  sudo postconf -e "smtp_tls_security_level = encrypt"
  sudo postconf -e "smtp_tls_wrappermode = yes"
else
  sudo postconf -e "smtp_tls_security_level = encrypt"
  sudo postconf -e "smtp_tls_wrappermode = no"
fi

sudo systemctl reload postfix
sudo postqueue -f

echo "Relayhost configured and queue flush requested."
