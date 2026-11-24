<?php
function agroSendMail(string $to, string $subject, string $body): bool
{
    if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $headers = ['MIME-Version' => '1.0', 'Content-Type' => 'text/plain; charset=UTF-8'];
    $from    = ini_get('sendmail_from');
    if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $headers['From'] = "AgroMarket Connect <$from>";
    }

    return mail($to, $subject, $body, $headers);
}

function queueAgroMail(mysqli $db, string $key, string $to, string $subject, string $body): void
{
    $db->execute_query('INSERT INTO transactional_emails(message_key,recipient,subject_line,body) VALUES(?,?,?,?)',
        [$key, $to, $subject, $body]);
}

function dispatchAgroMail(mysqli $db, string $key): void
{
    try {
        $db->execute_query('UPDATE transactional_emails SET retry_at=NOW()+INTERVAL 5 MINUTE
            WHERE message_key=? AND sent_at IS NULL AND retry_at<=NOW()', [$key]);
        if ($db->affected_rows !== 1) {
            return;
        }

        $row = $db->execute_query('SELECT * FROM transactional_emails WHERE message_key=?', [$key])->fetch_assoc();
        if (agroSendMail($row['recipient'], $row['subject_line'], $row['body'])) {
            $db->execute_query('UPDATE transactional_emails SET sent_at=NOW() WHERE message_key=?', [$key]);
        }
    } catch (Throwable $error) {
        error_log('AgroMarket email dispatch failed for ' . $key);
    }
}

function queueOrderEmails(mysqli $db, int $id, string $event): array
{
    $o = $db->execute_query("SELECT o.*,cl.crop_name,b.full_name buyer,b.email buyer_email,
        f.full_name farmer,f.email farmer_email,COALESCE(bc.amount,0) buyer_fee,COALESCE(fc.amount,0) farmer_fee
        FROM orders o JOIN crop_listings cl ON cl.listing_id=o.listing_id
        JOIN users b ON b.user_id=o.buyer_id JOIN users f ON f.user_id=o.farmer_id
        LEFT JOIN buyer_commissions bc ON bc.order_id=o.order_id
        LEFT JOIN order_commissions fc ON fc.order_id=o.order_id WHERE o.order_id=?", [$id])->fetch_assoc();
    $summary = "Order: {$o['order_number']}\nPlaced: {$o['placed_at']}\nStatus: {$o['order_status']}\n"
        . "Buyer: {$o['buyer']}\nFarmer: {$o['farmer']}\nCrop: {$o['crop_name']}\nQuantity: {$o['quantity']} {$o['quantity_unit']}\n"
        . "Unit price: INR {$o['unit_price']}\nCrop subtotal: INR {$o['subtotal']}\nFulfilment: {$o['fulfilment_method']}\n";
    if ($o['fulfilment_method'] === 'delivery') {
        $summary .= "Delivery address: {$o['delivery_address']}, {$o['delivery_city']}, {$o['delivery_state']} {$o['delivery_pincode']}\n";
    }
    $keys = [];
    foreach (['buyer', 'farmer'] as $role) {
        $key     = "order-$id-$event-$role";
        $amounts = $role === 'buyer'
            ? "Buyer fee: INR {$o['buyer_fee']}\nTotal including fee: INR " . number_format($o['subtotal'] + $o['buyer_fee'], 2)
            : "Farmer fee: INR {$o['farmer_fee']}\nProceeds after fee: INR " . number_format($o['subtotal'] - $o['farmer_fee'], 2);
        queueAgroMail($db, $key, $o[$role . '_email'], "AgroMarket order $event - {$o['order_number']}",
            "Hello {$o[$role]},\n\n$summary$amounts\n\nView this order in your AgroMarket dashboard.");
        $keys[] = $key;
    }
    return $keys;
}
