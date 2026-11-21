<?php
function barChart(array $values, string $caption): void
{
    $peak = max([1, ...array_values($values)]);
    echo '<figure><figcaption>' . h($caption) . '</figcaption><div class="bars">';
    foreach ($values as $name => $count) {
        $height = (int) round(100 * $count / $peak);
        echo '<div class="bar-col"><strong>' . number_format($count) . '</strong><div class="track"><i style="height:' . $height . '%"></i></div><span>' . h($name) . '</span></div>';
    }
    echo '</div></figure>';
}

function statusChart(array $groups): void
{
    $total = array_sum(array_column($groups, 'n'));
    if (! $total) {echo '<p class="empty">No orders in this period.</p>';return;}
    $colors = ['#256859', '#de9b4a', '#72839c', '#9b5671', '#93aa68', '#b1784f', '#576278', '#ac9775'];
    $stops  = [];
    $legend = '';
    $sum    = 0;
    foreach ($groups as $i => $group) {
        $color    = $colors[$i % count($colors)];
        $start    = 100 * $sum / $total;
        $sum     += (int) $group['n'];
        $end      = 100 * $sum / $total;
        $stops[]  = "$color $start% $end%";
        $legend  .= '<li><i style="background:' . $color . '"></i><span>' . h(label($group['status'])) . '</span><b>' . number_format($group['n']) . '</b></li>';
    }
    echo '<div class="mix"><div class="donut" aria-hidden="true" style="background:conic-gradient(' . implode(',', $stops) . ')"><b>' . number_format($total) . '</b></div>';
    echo '<ul class="legend" aria-label="Orders by status">' . $legend . '</ul></div>';
}
