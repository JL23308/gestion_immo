<?php
$privateKey = openssl_pkey_new([
    'digest_alg' => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
]);

openssl_pkey_export_to_file($privateKey, 'config/jwt.key');

$details = openssl_pkey_get_details($privateKey);
file_put_contents('config/jwt.pem', $details['key']);

echo "Keys generated successfully.\n";
