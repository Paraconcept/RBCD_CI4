<?php

/**
 * Retourne les initiales d'un prénom, en tenant compte des prénoms composés
 * séparés par un tiret ou une espace.
 * Ex : "Jean-Paul" → "JP."  |  "Marie Claire" → "MC."  |  "Patrick" → "P."
 */
function member_initials(string $firstName): string
{
    $parts = preg_split('/[-\s]+/', trim($firstName), -1, PREG_SPLIT_NO_EMPTY);
    return implode('-', array_map(
        static fn($p) => mb_strtoupper(mb_substr($p, 0, 1)),
        $parts
    )) . '.';
}
