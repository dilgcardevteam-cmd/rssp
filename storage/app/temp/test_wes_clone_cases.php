<?php
require __DIR__ . '/vendor/autoload.php';

template();

function template(): void {
    $template = __DIR__ . '/public/templates/WES_Template.docx';
    $out = __DIR__ . '/storage/app/temp';
    if (!is_dir($out)) {
        mkdir($out, 0777, true);
    }

    $cases = [
        [true, true],
        [true, false],
        [false, true],
        [false, false],
    ];

    foreach ($cases as [$replace, $index]) {
        $tp = new \PhpOffice\PhpWord\TemplateProcessor($template);
        $tp->setValue('name', 'TEST USER');
        $tp->setValue('date', 'April 07, 2026');
        $tp->cloneBlock('experience', 2, $replace, $index);

        if ($index) {
            for ($i = 1; $i <= 2; $i++) {
                $tp->setValue("from#$i", 'Jan 2024');
                $tp->setValue("to#$i", 'Present');
                $tp->setValue("position#$i", "Position $i");
                $tp->setValue("office#$i", "Office $i");
                $tp->setValue("supervisor#$i", "Supervisor $i");
                $tp->setValue("agency#$i", "Agency $i");
                $tp->setValue("accomplishments#$i", "- Acc $i");
                $tp->setValue("duties#$i", "- Duty $i");
            }
        } else {
            $tp->setValue('from', 'Jan 2024');
            $tp->setValue('to', 'Present');
            $tp->setValue('position', 'Position');
            $tp->setValue('office', 'Office');
            $tp->setValue('supervisor', 'Supervisor');
            $tp->setValue('agency', 'Agency');
            $tp->setValue('accomplishments', '- Acc');
            $tp->setValue('duties', '- Duty');
        }

        $name = 'wes_tpl_clone_case_replace' . ($replace ? 'T' : 'F') . '_index' . ($index ? 'T' : 'F');
        $tp->saveAs($out . '/' . $name . '.docx');
        echo $name, PHP_EOL;
    }
}
