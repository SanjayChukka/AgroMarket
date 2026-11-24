CREATE TABLE IF NOT EXISTS buyer_commissions (
    order_id BIGINT UNSIGNED PRIMARY KEY,
    rate_percent DECIMAL(5,2) NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    collected_at DATETIME NULL,
    collected_by BIGINT UNSIGNED NULL,
    receipt_reference VARCHAR(100) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS order_commissions (
    order_id BIGINT UNSIGNED PRIMARY KEY,
    rate_percent DECIMAL(5,2) NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    collected_at DATETIME NULL,
    collected_by BIGINT UNSIGNED NULL,
    receipt_reference VARCHAR(100) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB;
