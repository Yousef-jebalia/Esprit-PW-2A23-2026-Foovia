<?php
// Replace this with the key you generated in Google AI Studio (https://aistudio.google.com/apikey)
//Ask team for api key
// Model id for generativelanguage.googleapis.com/v1beta/models/{MODEL}:generateContent

define('GEMINI_MODEL', 'gemini-2.5-flash');//gemini-2.5-flash

// When true, JSON responses may include a "debug" object (HTTP code, curl errors, Gemini error body preview).
// Set to false once everything works, so visitors do not see technical details.
define('CHATBOT_DEBUG', true);
?>
