#!/usr/bin/env bash

# scripts/generate-jwt-keys.sh
# Generates ECDSA P-256 (ES256) key pair for JWT signing.
# Run once: task jwt:keys  (or bash scripts/generate-jwt-keys.sh)

set -euo pipefail

KEY_DIR="$(dirname "$0")/../config/jwt"
mkdir -p "$KEY_DIR"

PASSPHRASE="${JWT_PASSPHRASE:-change_me}"

echo "Generating ECDSA P-256 private key..."
openssl ecparam \
    -name prime256v1 \
    -genkey \
    -noout \
    -out "$KEY_DIR/private_raw.pem"

echo "Encrypting private key with passphrase..."
openssl ec \
    -in  "$KEY_DIR/private_raw.pem" \
    -out "$KEY_DIR/private.pem" \
    -aes256 \
    -passout "pass:$PASSPHRASE"

rm "$KEY_DIR/private_raw.pem"

echo "Extracting public key..."
openssl ec \
    -in      "$KEY_DIR/private.pem" \
    -pubout  \
    -out     "$KEY_DIR/public.pem" \
    -passin  "pass:$PASSPHRASE"

echo "Keys generated:"
echo "  Private: $KEY_DIR/private.pem"
echo "  Public:  $KEY_DIR/public.pem"

# Ensure keys are not world-readable
chmod 600 "$KEY_DIR/private.pem"
chmod 644 "$KEY_DIR/public.pem"
