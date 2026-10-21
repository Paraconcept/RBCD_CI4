<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Images qui défilent sur l'écran « Pause » du marquoir, après l'écusson du club.
 * Simples fichiers dans public/uploads : le nom commence par la date d'envoi, ce qui donne l'ordre de défilement.
 */
class ScoreboardPauseImages
{
    public const DIR     = 'uploads/scoreboard_pause/';
    public const MAX_KB  = 5120;
    private const EXT    = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const MIME   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** @return string[] noms de fichiers, dans l'ordre de défilement */
    public static function all(): array
    {
        $names = array_values(array_filter(
            array_map('basename', glob(FCPATH . self::DIR . '*') ?: []),
            static fn (string $n): bool => in_array(strtolower(pathinfo($n, PATHINFO_EXTENSION)), self::EXT, true)
        ));
        sort($names);
        return $names;
    }

    /** @return string[] */
    public static function urls(): array
    {
        return array_map(static fn (string $n): string => base_url(self::DIR . $n) . '?v=' . @filemtime(FCPATH . self::DIR . $n), self::all());
    }

    /** Retourne un message d'erreur, ou null si l'image est enregistrée. */
    public static function store(UploadedFile $file): ?string
    {
        if (!$file->isValid()) {
            return $file->getErrorString();
        }
        $ext = strtolower($file->getExtension() ?: $file->guessExtension() ?: '');
        if (!in_array($ext, self::EXT, true) || !in_array($file->getMimeType(), self::MIME, true)) {
            return '« ' . $file->getClientName() . ' » n\'est pas une image JPEG, PNG, WebP ou GIF.';
        }
        if ($file->getSizeByUnit('kb') > self::MAX_KB) {
            return '« ' . $file->getClientName() . ' » dépasse ' . (self::MAX_KB / 1024) . ' Mo.';
        }

        if (!is_dir(FCPATH . self::DIR)) {
            mkdir(FCPATH . self::DIR, 0755, true);
        }
        $file->move(FCPATH . self::DIR, date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext);
        return null;
    }

    public static function delete(string $name): bool
    {
        // Seuls les fichiers réellement listés peuvent être supprimés (pas de chemin arbitraire)
        if (!in_array($name, self::all(), true)) {
            return false;
        }
        return @unlink(FCPATH . self::DIR . $name);
    }
}
