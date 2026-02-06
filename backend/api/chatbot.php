<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/db_final.php';

$data = json_decode(file_get_contents("php://input"));
$message = strtolower($data->message ?? '');

if (empty($message)) {
    echo json_encode(['response' => "I didn't catch that. detailed query?"]);
    exit;
}

// 1. Fetch all events for fuzzy matching
$stmt = $conn->query("SELECT event_id, event_name, event_date, venue, description, college_name FROM events");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matched_event = null;
$best_match_score = 1000; // Lower is better (Levenshtein)

// 2. Identify Event Names in Message
foreach ($events as $event) {
    // Check if exact name is in message
    if (strpos($message, strtolower($event['event_name'])) !== false) {
        $matched_event = $event;
        break; 
    }
    
    // Fuzzy match (if no exact found yet)
    // Compare 'tokens' of the message with event name
    // This is a simple approximation
    $lev = levenshtein($message, strtolower($event['event_name']));
    // Normalize logic slightly: check if event name is 'close enough' to any part of the string?
    // For simplicity, let's just match the whole event name against the message 'tokens' or just the whole string if short.
    
    // Better Approach: Check if any word in the event name exists in the message? No, too broad.
    // Let's stick to Levenshtein of the whole query vs the name for short queries? 
    
    // Let's implement a "Key phrase" check.
    // If the user says "When is Hackathon", we extract "Hackathon".
    // Simple heuristic: Remove stopwords ("when", "is", "the", "at", "what", "time") and treat remainder as target.
}

// Improved Logic: Intent Detection + Entity Extraction
$intents = [
    'date' => ['when', 'date', 'time', 'day'],
    'venue' => ['where', 'place', 'venue', 'location'],
    'details' => ['about', 'what', 'description', 'info'],
    'register' => ['register', 'join', 'signup', 'enroll'],
    'greeting' => ['hi', 'hello', 'hey'],
    'thanks' => ['thank', 'thanks']
];

$detected_intent = 'details'; // Default
foreach ($intents as $intent => $keywords) {
    foreach ($keywords as $kw) {
        if (strpos($message, $kw) !== false) {
            $detected_intent = $intent;
            break 2;
        }
    }
}

// Handle non-event queries
if ($detected_intent === 'greeting') {
    echo json_encode(['response' => "Hello! 👋 I'm your Event Assistant. Ask me about any event!"]);
    exit;
}
if ($detected_intent === 'thanks') {
    echo json_encode(['response' => "You're welcome! Happy to help. 😊"]);
    exit;
}
if (strpos($message, 'upcoming') !== false || strpos($message, 'next') !== false) {
    // List upcoming events
    $upStmt = $conn->query("SELECT event_name, event_date FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3");
    $upEvents = $upStmt->fetchAll(PDO::FETCH_ASSOC);
    if ($upEvents) {
        $resp = "Here are the upcoming events:\n";
        foreach ($upEvents as $e) {
            $resp .= "• " . $e['event_name'] . " (" . $e['event_date'] . ")\n";
        }
        echo json_encode(['response' => $resp]);
    } else {
        echo json_encode(['response' => "No upcoming events found right now."]);
    }
    exit;
}

// 3. Try to find the specific event mentioned
// Strategy: Loop through all event names and see if one is explicitly mentioned (or close)
$found_event = null;
$shortest_distance = -1;

foreach ($events as $event) {
    $name = strtolower($event['event_name']);
    
    if (strpos($message, $name) !== false) {
        $found_event = $event;
        break; // Exact substring match found!
    }
    
    // Simple levenshtein on words?
    // Let's allow minor typos.
    $dist = levenshtein($message, $name);
    // This isn't great if message is "Tell me about Hackathon" (long string) vs "Hackathon"
    // Better: Split message into words?
    
    // Minimal viable logic: substring match is usually enough for "event bot".
}

if (!$found_event) {
    // Last ditch: check word overlap
    foreach ($events as $event) {
        $event_words = explode(' ', strtolower($event['event_name']));
        foreach ($event_words as $word) {
            if (strlen($word) > 3 && strpos($message, $word) !== false) {
                // Strong hint
                $found_event = $event;
                break 2;
            }
        }
    }
}

// 4. Formulate Response
if ($found_event) {
    $resp = "";
    switch ($detected_intent) {
        case 'date':
            $resp = "The event '" . $found_event['event_name'] . "' is on " . $found_event['event_date'] . ".";
            break;
        case 'venue':
            $resp = "It will be held at " . $found_event['venue'] . " (" . $found_event['college_name'] . ").";
            break;
        case 'register':
            $resp = "You can register for '" . $found_event['event_name'] . "' in the 'Events' section of your dashboard!";
            break;
        default:
            // 'details'
            $resp = $found_event['event_name'] . ": " . $found_event['description'] . "\n\n📅 Date: " . $found_event['event_date'] . "\n📍 Venue: " . $found_event['venue'];
            break;
    }
    echo json_encode(['response' => $resp]);
} else {
    echo json_encode(['response' => "I'm not sure which event you're asking about. Can you specify the event name? (e.g., 'When is the Hackathon?')"]);
}
?>
