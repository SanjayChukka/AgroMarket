
<?php

    /* Bar Chart */
    function barChart(array $values, string $caption): void
    {
    $peak = max([1, ...array_values($values)]);

    echo '<figure>';
    echo '<figcaption>' . h($caption) . '</figcaption>';
    echo '<div class="bars">';

    foreach ($values as $name => $count) {

        $height = (int) round(100 * $count / $peak);

        echo '<div class="bar-col">';

        echo '<strong>' .
        number_format($count) .
            '</strong>';

        echo '<div class="track">';
        echo '<i style="height:' . $height . '%"></i>';
        echo '</div>';

        echo '<span>' .
        h($name) .
            '</span>';

        echo '</div>';
    }

    echo '</div>';
    echo '</figure>';
    }

    /* Order Status Donut Chart */
    function statusChart(array $groups): void
    {
    $total = array_sum(array_column($groups, 'n'));

    /* Handle Empty Data */
    if (! $total) {
        echo '<p class="empty">No orders in this period.</p>';
        return;
    }

    /* Chart Colors */
    $colors = [
        '#256859',
        '#de9b4a',
        '#72839c',
        '#9b5671',
        '#93aa68',
        '#b1784f',
        '#576278',
        '#ac9775',
    ];

    $stops  = [];
    $legend = '';
    $sum    = 0;

    /* Prepare Chart Segments */
    foreach ($groups as $i => $group) {

        $color = $colors[$i % count($colors)];

        $start = 100 * $sum / $total;

        $sum += (int) $group['n'];

        $end = 100 * $sum / $total;

        $stops[] = "$color $start% $end%";

        /* Legend Item */
        $legend .=
        '<li>' .
        '<i style="background:' . $color . '"></i>' .
        '<span>' . h(label($group['status'])) . '</span>' .
        '<b>' . number_format($group['n']) . '</b>' .
            '</li>';
    }

    /* Donut Chart */
    echo '<div class="mix">';

    echo '<div
        class="donut"
        aria-hidden="true"
        style="background:conic-gradient(' . implode(',', $stops) . ')">';

    echo '<b>' . number_format($total) . '</b>';

    echo '</div>';

    /* Chart Legend */
    echo '<ul class="legend" aria-label="Orders by status">';
    echo $legend;
    echo '</ul>';

    echo '</div>';
}
