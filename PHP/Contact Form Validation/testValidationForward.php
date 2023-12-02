<?php
foreach ($_POST as $key => $value) {
    sendDiscordMessage("Field " . htmlspecialchars($key) . " is " . htmlspecialchars($value));
}
echo 'https://www.ipqualityscore.com/';

function sendDiscordMessage($message)
{
    $webhookurl = "DISCORD WEB HOOK";

    //=======================================================================================================
    // Compose message. You can use Markdown
    // Message Formatting -- https://discordapp.com/developers/docs/reference#message-formatting
    //========================================================================================================

    $timestamp = date("c", strtotime("now"));

    $json_data = json_encode([
        // Message
        "content" => $message,

        // Username
        "username" => "Captain Hook",

        // Embeds Array
        "embeds" => [
            [
                // Embed Title
                "title" => "IPQS PHP Validation Form Test",
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


    $ch = curl_init($webhookurl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
    // echo $response;
    curl_close($ch);
}
