<?php
// Appel du bar (Verr. Num) : uniquement sur un Chromebook du marquoir qui sait de quel billard il s'agit
helper('cookie');
$barTable = isset($table) && is_object($table) ? $table
    : (new \App\Models\ScoreboardTableModel())->find((int) get_cookie(\App\Filters\ScoreboardDeviceFilter::TABLE_COOKIE));
if ($barTable && $barTable->is_active): ?>
<script>window.SB_BAR = <?= json_encode(['url' => base_url('marquoir/api/bar/appel/' . $barTable->id), 'table' => $barTable->name], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= base_url('assets/scoreboard/bar-call.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/bar-call.js') ?>"></script>
<?php endif; ?>
