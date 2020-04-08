<?php
require_once __DIR__ . '/../app/bootstrap.php';

use \RedBeanPHP\R as R;

R::selectDatabase('votacao');
R::useFeatureSet('latest');

$hash = 'hash001';

$sessao = R::findOne('sessao', 'hash = ?', [$hash]);

$logo2 = '';

$filename = gerarListaQrcodePdf($sessao, $logo2);
