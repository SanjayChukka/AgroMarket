-- Import once in the agromarket database. Existing commission receipts stay valid.
CREATE TABLE IF NOT EXISTS commission_bank_credits (
    bank_reference VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    payer ENUM('farmer','buyer') NOT NULL,
    credited_amount DECIMAL(14,2) NOT NULL,
    recorded_by BIGINT UNSIGNED NOT NULL,
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY order_fee (order_id, payer),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (recorded_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS crop_payment_receipts (
    order_id BIGINT UNSIGNED PRIMARY KEY,
    farmer_id BIGINT UNSIGNED NOT NULL,
    confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (farmer_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
