<?php

/**
 * Component: step_progress.php
 * Usage: view('App\Views\Layouts\Components\step_progress', ['currentStep' => $currentStep, 'steps' => $steps])
 */

$iconPaths = [
    'user'       => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'users'      => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
    'graduation' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
    'upload'     => '<polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/>',
    'check'      => '<polyline points="20 6 9 17 4 12"/>',
    'send'       => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
];
?>

<div class="mb-8">
    <p class="text-sm text-center mb-4" style="color:hsl(220,15%,50%);">
        Langkah <strong style="color:hsl(220,54%,20%);"><?= $currentStep ?></strong> dari <strong style="color:hsl(220,54%,20%);"><?= count($steps) ?></strong>
        &mdash; <span style="color:hsl(220,54%,20%);font-weight:600;"><?= esc($steps[$currentStep]['label'] ?? '') ?></span>
    </p>

    <div class="flex items-center">
        <?php foreach ($steps as $num => $step):
            $done    = $num < $currentStep;
            $current = $num === $currentStep;
            $iconKey = $step['icon'] ?? 'check';
            $iconD   = $iconPaths[$iconKey] ?? $iconPaths['check'];
        ?>
            <div class="flex flex-col items-center flex-1">
                <div class="flex items-center w-full">
                    <?php if ($num > 1): ?>
                        <div class="flex-1 h-0.5 rounded" style="background:<?= ($num <= $currentStep) ? 'hsl(142,71%,45%)' : 'hsl(220,20%,88%)' ?>;"></div>
                    <?php else: ?>
                        <div class="flex-1"></div>
                    <?php endif; ?>

                    <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 flex-shrink-0 transition-all"
                        style="<?php
                                if ($done)         echo 'background:hsl(142,71%,45%);border-color:hsl(142,71%,45%);color:white;';
                                elseif ($current)  echo 'background:hsl(220,54%,20%);border-color:hsl(220,54%,20%);color:hsl(43,70%,80%);';
                                else               echo 'background:hsl(220,20%,96%);border-color:hsl(220,20%,82%);color:hsl(220,15%,55%);';
                                ?>">
                        <?php if ($done): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        <?php else: ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><?= $iconD ?></svg>
                        <?php endif; ?>
                    </div>

                    <?php if ($num < count($steps)): ?>
                        <div class="flex-1 h-0.5 rounded" style="background:<?= $done ? 'hsl(142,71%,45%)' : 'hsl(220,20%,88%)' ?>;"></div>
                    <?php else: ?>
                        <div class="flex-1"></div>
                    <?php endif; ?>
                </div>

                <p class="hidden sm:block text-xs mt-2 text-center font-medium"
                    style="color:<?php
                                    if ($done)        echo 'hsl(142,60%,35%)';
                                    elseif ($current) echo 'hsl(220,54%,20%)';
                                    else              echo 'hsl(220,15%,55%)';
                                    ?>;">
                    <?= esc($step['label']) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</div>