
<?php

// Send an email using the configured mail service.
function agroSendMail(
    string $to,
    string $subject,
    string $body
): bool {

    // Validate the recipient's email address.
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Configure email headers.
    $headers = [
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8'
    ];

    $from = ini_get('sendmail_from');

    // Set the sender's email address.
    if (filter_var($from, FILTER_VALIDATE_EMAIL)) {

        $headers['From'] = "AgroMarket Connect <$from>";
    }

    return mail($to, $subject, $body, $headers);
}


// Store an email in the transactional email queue.
function queueAgroMail(
    mysqli $db,
    string $key,
    string $to,
    string $subject,
    string $body
): void {

    $db->execute_query(
        'INSERT INTO transactional_emails (
            message_key,
            recipient,
            subject_line,
            body
        ) VALUES (?, ?, ?, ?)',
        [
            $key,
            $to,
            $subject,
            $body
        ]
    );
}


// Dispatch a queued transactional email.
function dispatchAgroMail(
    mysqli $db,
    string $key
): void {

    try {

        // Reserve the email and schedule the next retry.
        $db->execute_query(
            'UPDATE transactional_emails
             SET retry_at = NOW() + INTERVAL 5 MINUTE
             WHERE message_key = ?
                AND sent_at IS NULL
                AND retry_at <= NOW()',
            [$key]
        );

        // Prevent duplicate or premature dispatch attempts.
        if ($db->affected_rows !== 1) {
            return;
        }


        // Retrieve the queued email details.
        $row = $db->execute_query(
            'SELECT *
             FROM transactional_emails
             WHERE message_key = ?',
            [$key]
        )->fetch_assoc();


        // Attempt to send the email.
        if (
            agroSendMail(
                $row['recipient'],
                $row['subject_line'],
                $row['body']
            )
        ) {

            // Mark the email as successfully sent.
            $db->execute_query(
                'UPDATE transactional_emails
                 SET sent_at = NOW()
                 WHERE message_key = ?',
                [$key]
            );
        }

    } catch (Throwable $error) {

        // Log unexpected email dispatch errors.
        error_log(
            'AgroMarket email dispatch failed for ' . $key
        );
    }
}


// Generate and queue order-related emails for buyers and farmers.
function queueOrderEmails(
    mysqli $db,
    int $id,
    string $event
): array {

    // Fetch order, crop, buyer, farmer and commission details.
    $o = $db->execute_query(
        "SELECT
            o.*,
            cl.crop_name,
            b.full_name buyer,
            b.email buyer_email,
            f.full_name farmer,
            f.email farmer_email,
            COALESCE(bc.amount, 0) buyer_fee,
            COALESCE(fc.amount, 0) farmer_fee

        FROM orders o

        JOIN crop_listings cl
            ON cl.listing_id = o.listing_id

        JOIN users b
            ON b.user_id = o.buyer_id

        JOIN users f
            ON f.user_id = o.farmer_id

        LEFT JOIN buyer_commissions bc
            ON bc.order_id = o.order_id

        LEFT JOIN order_commissions fc
            ON fc.order_id = o.order_id

        WHERE o.order_id = ?",
        [$id]
    )->fetch_assoc();


    // Prepare the common order summary.
    $summary =
          "Order: {$o['order_number']}\n"
        . "Placed: {$o['placed_at']}\n"
        . "Status: {$o['order_status']}\n"
        . "Buyer: {$o['buyer']}\n"
        . "Farmer: {$o['farmer']}\n"
        . "Crop: {$o['crop_name']}\n"
        . "Quantity: {$o['quantity']} {$o['quantity_unit']}\n"
        . "Unit price: INR {$o['unit_price']}\n"
        . "Crop subtotal: INR {$o['subtotal']}\n"
        . "Fulfilment: {$o['fulfilment_method']}\n";


    // Include delivery address when delivery is selected.
    if ($o['fulfilment_method'] === 'delivery') {

        $summary .=
            "Delivery address: {$o['delivery_address']}, "
            . "{$o['delivery_city']}, "
            . "{$o['delivery_state']} "
            . "{$o['delivery_pincode']}\n";
    }


    // Initialize the queued email keys.
    $keys = [];


    // Generate separate emails for the buyer and farmer.
    foreach (['buyer', 'farmer'] as $role) {

        // Generate a unique email message key.
        $key = "order-$id-$event-$role";


        // Prepare role-specific commission and payment details.
        $amounts = $role === 'buyer'

            ? "Buyer fee: INR {$o['buyer_fee']}\n"
                . "Total including fee: INR "
                . number_format(
                    $o['subtotal'] + $o['buyer_fee'],
                    2
                )

            : "Farmer fee: INR {$o['farmer_fee']}\n"
                . "Proceeds after fee: INR "
                . number_format(
                    $o['subtotal'] - $o['farmer_fee'],
                    2
                );


        // Queue the order notification email.
        queueAgroMail(
            $db,
            $key,
            $o[$role . '_email'],
            "AgroMarket order $event - {$o['order_number']}",
            "Hello {$o[$role]},\n\n"
                . "$summary$amounts\n\n"
                . "View this order in your AgroMarket dashboard."
        );


        // Store the queued message key.
        $keys[] = $key;
    }


    // Return the queued email keys for dispatch.
    return $keys;
}
