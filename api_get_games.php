<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$ps4_file = DATA_PATH . 'ps4-games.json';
$ps5_file = DATA_PATH . 'ps5-games.json';
$ps3_file = DATA_PATH . 'ps3-games.json';

$ps4_games = file_exists($ps4_file) ? json_decode(file_get_contents($ps4_file), true) : [];
$ps5_games = file_exists($ps5_file) ? json_decode(file_get_contents($ps5_file), true) : [];
$ps3_games = file_exists($ps3_file) ? json_decode(file_get_contents($ps3_file), true) : [];

// دمج جميع الألعاب في مصفوفة واحدة
$all_games = array_merge($ps4_games, $ps5_games, $ps3_games);

echo json_encode($all_games, JSON_UNESCAPED_UNICODE);
