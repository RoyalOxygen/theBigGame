<?php layout_start('admin', ['title' => $title, 'admin' => $admin]); ?>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Registered applicants', $stats['users']],
        ['Verified accounts', $stats['verified']],
        ['Applications', $stats['applications']],
        ['Pending / submitted', $stats['pending']],
        ['Under review', $stats['under_review']],
        ['Approved', $stats['approved']],
        ['Rejected', $stats['rejected']],
        ['Approved grant value', money_ngn($stats['grant_value'])],
        ['Card requests', $stats['cards']],
        ['Awaiting payment', $stats['card_pay']],
        ['Cards processing', $stats['card_proc']],
        ['Ready for collection', $stats['card_ready']],
    ];
    foreach ($cards as $c): ?>
        <div class="col-6 col-lg-3">
            <div class="card-soft stat">
                <div class="small text-muted"><?= e($c[0]) ?></div>
                <strong><?= e((string) $c[1]) ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft">
            <h2 class="h6">Application outcomes</h2>
            <canvas id="statusChart" height="140"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft">
            <h2 class="h6">Recent activity</h2>
            <?php foreach ($activity as $row): ?>
                <div class="border-bottom py-2 small">
                    <strong><?= e($row['action']) ?></strong>
                    <div class="text-muted"><?= e($row['first_name'] . ' ' . $row['last_name']) ?> &middot; <?= e($row['created_at']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
$chartsScript = 'new Chart(document.getElementById("statusChart"),{type:"bar",data:{labels:["Submitted","Under review","Approved","Rejected"],datasets:[{label:"Applications",data:['.(int)$stats['pending'].','.(int)$stats['under_review'].','.(int)$stats['approved'].','.(int)$stats['rejected'].'],backgroundColor:["#0b5c35","#c9a227","#148c52","#a33"]}]},options:{plugins:{legend:{display:false}}}});';
layout_end('admin', ['chartsScript' => $chartsScript]);
?>
