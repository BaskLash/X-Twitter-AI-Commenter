<?php

/*
----------------------------------------------------
 SIMPLE AI BACKEND API
 Single-file backend for Chrome Extensions
----------------------------------------------------
*/

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

/*
----------------------------------------------------
 CONFIG
----------------------------------------------------
*/

$OPENAI_API_KEY = "";
$MODEL = "gpt-4o-mini";

/*
----------------------------------------------------
 GET REQUEST DATA
----------------------------------------------------
*/

$input = json_decode(file_get_contents("php://input"), true);

$action = $input["action"] ?? "";
$tweet  = $input["tweet"] ?? "";
$url    = $input["url"] ?? "";

/*
----------------------------------------------------
 PROMPTS
----------------------------------------------------
*/

$systemPrompt = "You are a helpful assistant that writes high quality social media replies.";

$prompts = [

    "prompt" => "
Write a helpful response related to the tweet below.
Keep it natural and short.

Tweet:
$tweet
",

    "templates" => "
Write a friendly response using a conversational tone.

Tweet:
$tweet
",

    "ai-reply" => "
Write a high quality reply to this tweet.
The reply should sound human and thoughtful.

Tweet:
$tweet
",

    "regenerate" => "
Rewrite a new version of a reply to this tweet.
Make it slightly different and engaging.

Tweet:
$tweet
"

];

/*
----------------------------------------------------
 VALIDATE ACTION
----------------------------------------------------
*/

if (!isset($prompts[$action])) {

    echo json_encode([
        "error" => "Invalid action"
    ]);

    exit;

}

$userPrompt = $prompts[$action];

/*
----------------------------------------------------
 CALL OPENAI API
----------------------------------------------------
*/

$payload = [
    "model" => $MODEL,
    "messages" => [
        [
            "role" => "system",
            "content" => $systemPrompt
        ],
        [
            "role" => "user",
            "content" => $userPrompt
        ]
    ],
    "temperature" => 0.7
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $OPENAI_API_KEY
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);

if (curl_errno($ch)) {

    echo json_encode([
        "error" => "Curl error: " . curl_error($ch)
    ]);

    exit;

}

curl_close($ch);

$data = json_decode($response, true);

/*
----------------------------------------------------
 EXTRACT AI RESPONSE
----------------------------------------------------
*/

$reply = $data["choices"][0]["message"]["content"] ?? null;

if (!$reply) {

    echo json_encode([
        "error" => "No AI response"
    ]);

    exit;

}

/*
----------------------------------------------------
 RETURN RESPONSE
----------------------------------------------------
*/

echo json_encode([
    "success" => true,
    "reply" => trim($reply)
]);

exit;

?>