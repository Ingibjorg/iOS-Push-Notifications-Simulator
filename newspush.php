<?php

// Put your device token here (without spaces):
$deviceToken = '9b9b92e1719c3535fdeeaaafd00e8b53cdd9444a3315439ca6a3b7e14dbb21eb';

// Put your private key's passphrase here:
$passphrase = 'pushcert';

$message = $argv[1];
$url = $argv[2];

if (!$message || !$url)
    exit('Example Usage: $php newspush.php \'Breaking News!\' \'https://raywenderlich.com\'' . "\n");

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushcert.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
  'ssl://gateway.sandbox.push.apple.com:2195', $err,
  $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
  exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
$basicBody['aps'] = array(
  'alert' => $message,
  'sound' => 'default',
  'link_url' => $url,
  );

$customBody['custom_json'] = array(
    'saleNumber' => "N09520",
);
  
$body = array_merge($basicBody, $customBody);

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
  echo 'Message not delivered' . PHP_EOL;
else
  echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);
