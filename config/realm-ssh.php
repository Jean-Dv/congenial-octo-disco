<?php

return [
    'binary' => env('REALM_SSH_BINARY', '/usr/bin/ssh'),
    'known_hosts_file' => env('REALM_SSH_KNOWN_HOSTS_FILE', '/etc/ssh/ssh_known_hosts'),
    'temporary_directory' => env('REALM_SSH_TEMPORARY_DIRECTORY', sys_get_temp_dir()),
    'connect_timeout' => (int) env('REALM_SSH_CONNECT_TIMEOUT', 8),
    'startup_timeout' => (int) env('REALM_SSH_STARTUP_TIMEOUT', 10),
    'server_alive_interval' => (int) env('REALM_SSH_SERVER_ALIVE_INTERVAL', 30),
    'server_alive_count_max' => (int) env('REALM_SSH_SERVER_ALIVE_COUNT_MAX', 3),
];
