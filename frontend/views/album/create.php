<?php
$this->title = 'Create album'; 

echo $this->render('_form', [
    'model' => $model,
    'trackOptions' => $trackOptions,
    'selectedTrackIds' => $selectedTrackIds,
]);
