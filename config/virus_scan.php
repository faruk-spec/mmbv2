<?php
return [
    'virustotal_api_key' => getenv('VIRUSTOTAL_API_KEY') ?: '',
    'max_file_size_mb'   => 50,
    'blocked_domains'    => [],
];
