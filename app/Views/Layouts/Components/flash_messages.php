<?php
$types = [
    'success' => ['bg' => 'bg-green-50',  'border' => 'border-green-200', 'text' => 'text-green-800',  'icon' => 'fa-check-circle',       'icon_color' => 'text-green-500'],
    'error'   => ['bg' => 'bg-red-50',    'border' => 'border-red-200',   'text' => 'text-red-800',    'icon' => 'fa-exclamation-circle', 'icon_color' => 'text-red-500'],
    'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800', 'icon' => 'fa-exclamation-triangle', 'icon_color' => 'text-yellow-500'],
    'info'    => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',  'text' => 'text-blue-800',   'icon' => 'fa-info-circle',        'icon_color' => 'text-blue-500'],
];

foreach ($types as $type => $style):
    $msg = session()->getFlashdata($type);
    if ($msg):
?>
        <div x-data="{ show: true }" x-show="show" x-transition
            class="mb-3 flex items-start gap-3 p-4 rounded-xl border <?= $style['bg'] ?> <?= $style['border'] ?> <?= $style['text'] ?>">
            <i class="fas <?= $style['icon'] ?> <?= $style['icon_color'] ?> mt-0.5 flex-shrink-0"></i>
            <p class="text-sm flex-1"><?= esc($msg) ?></p>
            <button @click="show = false" class="flex-shrink-0 opacity-50 hover:opacity-100 ml-auto">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    <?php
    endif;
endforeach;

// Error list (dari validator)
$errors = session()->getFlashdata('errors');
if ($errors && is_array($errors)):
    ?>
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-3 p-4 rounded-xl border bg-red-50 border-red-200 text-red-800">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
            <div class="flex-1">
                <p class="text-sm font-medium mb-1">Harap perbaiki kesalahan berikut:</p>
                <ul class="text-sm list-disc list-inside space-y-0.5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button @click="show = false" class="opacity-50 hover:opacity-100">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    </div>
<?php endif; ?>