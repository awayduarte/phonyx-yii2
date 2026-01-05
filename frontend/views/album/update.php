<?php
$this->title = 'Edit album';

echo $this->render('_form', [
    'model' => $model,
    'trackOptions' => $trackOptions,
    'selectedTrackIds' => $selectedTrackIds,
]);
