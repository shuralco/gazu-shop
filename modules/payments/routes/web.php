<?php

// Payment callback routes. Підключаються через ModuleDiscovery
// якщо payments module увімкнено. Disable → endpoint 404,
// shop pivot на manual payment.
//
// Конкретні route definitions знаходяться у core routes/web.php
// (Route::post('/payment/callback/{gateway}', ...)) — будуть перенесені
// у наступному sprint коли PaymentController буде в модулі.
