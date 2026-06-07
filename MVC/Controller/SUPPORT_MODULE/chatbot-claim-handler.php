<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/chatbot-claim-service.php';

chatbot_claim_send_json_response(
    chatbot_claim_process_session_request(file_get_contents('php://input'))
);
