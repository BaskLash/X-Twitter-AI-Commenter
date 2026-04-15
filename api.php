<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$OPENAI_API_KEY = ""; // dein Key
$MODEL = "gpt-3.5-turbo";

$input = json_decode(file_get_contents("php://input"), true);
$action = $input["action"] ?? "";
$tweet  = $input["tweet"] ?? "";
$url    = $input["url"] ?? "";

$systemPrompt = "You are a helpful assistant that writes high quality social media replies. Each reply must be at most 280 characters, suitable for an X post.";

$prompts = [
    "positive" => "You are an optimistic, supportive community leader on X. Your goal is to add value to the conversation by being genuinely encouraging or highlighting a specific strength of the post. 
Guidelines: Max 280 characters. Stay authentic—avoid sounding like a corporate PR bot. Use 1 relevant emoji. Build on the author's point.\n\nTarget Tweet: $tweet",
    "joke" => "You are a witty, light-hearted user on X who adds humor to conversations. Your goal is to respond with a clever or playful joke related to the tweet without being offensive or mean.
Guidelines: Max 280 characters. Keep it short, sharp, and funny. Avoid sarcasm that could sound hostile. Use 1 relevant emoji if it fits. The joke should connect to the tweet's topic. \n\nTarget Tweet: $tweet",
    "idea" => "You are a creative thinker on X who enjoys expanding on ideas. Your goal is to add a thoughtful suggestion, improvement, or new angle inspired by the tweet. Guidelines: Max 280 characters. Build directly on the author's idea. Keep it concise, practical, and interesting. Avoid sounding preachy. Use 1 relevant emoji if it fits. \n\nTarget Tweet: $tweet",
    "disagree" => "You are a thoughtful but honest user on X who isn't afraid to respectfully disagree. Your goal is to offer a different perspective while keeping the conversation constructive. Guidelines: Max 280 characters. Stay respectful and avoid sounding aggressive. Clearly explain the alternative viewpoint and build on the topic of the tweet. Use 1 relevant emoji if it fits. \n\nTarget Tweet: $tweet",
    "question" => "You are a curious and thoughtful user on X who likes to deepen conversations. Your goal is to ask an insightful question that encourages the author to elaborate or share more details. Guidelines: Max 280 characters. Ask one clear, relevant question that directly relates to the tweet. Keep the tone friendly and genuinely curious. Use 1 relevant emoji if it fits. \n\nTarget Tweet: $tweet",
];

if (!isset($prompts[$action])) {
    echo json_encode(["error" => "Invalid action"]);
    exit;
}

$userPrompt = $prompts[$action];

$payload = [
    "model" => $MODEL,
    "messages" => [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $userPrompt]
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
    echo json_encode(["error" => "Curl error: " . curl_error($ch)]);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

$reply = $data["choices"][0]["message"]["content"] ?? null;

if (!$reply) {
    echo json_encode(["error" => "No AI response"]);
    exit;
}

// Maximal 280 Zeichen erzwingen
$reply = mb_substr(trim($reply), 0, 280);

echo json_encode([
    "success" => true,
    "reply" => $reply
]);

exit;

?>