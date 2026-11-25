
<?php

    // Called only from a role-protected orders page.
    function showOrderMoney(
    mysqli $link,
    string $role,
    int $userId
    ): void {

    // Determine the commission table and order owner based on the role.
    $farmer = $role === 'farmer';

    $fees = $farmer
        ? 'order_commissions'
        : 'buyer_commissions';

    $owner = $farmer
        ? 'farmer_id'
        : 'buyer_id';

    $operator = $farmer ? '-' : '+';

    // Calculate earnings, spending, and confirmed payments.
    $amounts = $link->execute_query(
        "SELECT

            COALESCE(
                SUM(o.subtotal $operator COALESCE(c.amount, 0)),
                0
            ) AS total,

            COALESCE(
                SUM(
                    CASE
                        WHEN r.order_id IS NOT NULL
                        THEN o.subtotal
                        ELSE 0
                    END
                ),
                0
            ) AS crop_paid,

            COALESCE(
                SUM(
                    CASE
                        WHEN c.collected_at IS NOT NULL
                        THEN c.amount
                        ELSE 0
                    END
                ),
                0
            ) AS fee_paid,

            COALESCE(
                SUM(
                    CASE
                        WHEN r.order_id IS NOT NULL
                        THEN o.subtotal
                        ELSE 0
                    END
                )
                $operator
                SUM(
                    CASE
                        WHEN c.collected_at IS NOT NULL
                        THEN c.amount
                        ELSE 0
                    END
                ),
                0
            ) AS confirmed

        FROM orders o

        LEFT JOIN $fees c
            ON c.order_id = o.order_id

        LEFT JOIN crop_payment_receipts r
            ON r.order_id = o.order_id

        WHERE o.$owner = ?
            AND o.order_status = 'completed'",
        [$userId]
    )->fetch_assoc();

    // Define role-specific payment summary labels.
    $labels = [
        'total'     => $farmer
            ? 'Net earnings after fees'
            : 'Total purchase cost',

        'crop_paid' => 'Crop payments confirmed by farmer',

        'fee_paid'  => 'Commission paid',

        'confirmed' => $farmer
            ? 'Net amount received'
            : 'Total spent and confirmed',
    ];

    ?>


    <!-- Completed Order Payment Summary -->
    <section aria-label="Completed order payments">

        <!-- Role-specific Section Heading -->
        <h2>
            <?php echo $farmer ? 'Earnings' : 'Spending'; ?>
            on completed orders
        </h2>


        <!-- Payment Summary Cards -->
        <div class="money-grid">

            <?php foreach ($labels as $key => $label): ?>

                <div class="money-card">

                    <!-- Payment Label -->
                    <span>
                        <?php echo e($label); ?>
                    </span>

                    <!-- Formatted Payment Amount -->
                    <strong>
                        ₹<?php echo number_format(
                                       (float) $amounts[$key],
                                       2
                               ); ?>
                    </strong>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

    <?php
    }
