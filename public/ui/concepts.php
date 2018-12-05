<?php
const MAXLEVEL = 3;
const MAXKIDS = 5;
const PAGE = 10;

$id = $_GET['id'] ?? 42;

$text = <<<EOS
Alohamora wand elf parchment, Wingardium Leviosa hippogriff, house dementors betrayal. Holly, Snape centaur portkey ghost Hermione spell bezoar Scabbers. Peruvian-Night-Powder werewolf, Dobby pear-tickle half-moon-glasses, Knight-Bus.

Padfoot snargaluff seeker: Hagrid broomstick mischief managed. Snitch Fluffy rock-cake, 9 ¾ dress robes I must not tell lies. Mudbloods yew pumpkin juice phials Ravenclaw’s Diadem 10 galleons Thieves Downfall. Ministry-of-Magic mimubulus mimbletonia Pigwidgeon knut phoenix feather other minister Azkaban.
EOS;

function kids($id, $level)
{
    if ($level > MAXLEVEL) {
        return [];
    }
    $kids = [];

    for ($i = 1; $i <= MAXKIDS; $i++) {
        $kid = node($id . '.' . $i, $level);
        if ($kid !== null) {
            $kids[] = $kid;
        }
    }
    return $kids;
}

function node($id, $level = 0)
{
    global $text;

    $kids = kids($id, $level + 1);

    return [
        'id' => $id,
        'title' => "Node #{$id}",
        'body' => nl2br("Dies ist der Body von Node #{$id}. " . $text),
        'kids' => $kids,
        'open' => $level <= 1 && count($kids) && !rand(0, 2) ? true : false,
    ];
}

$tree = node($id);

header('Content-type: application/json');
echo json_encode($tree, JSON_PRETTY_PRINT);
