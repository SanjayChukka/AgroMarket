-- Associate one submitted checkout form with one order, including concurrent retries.
CREATE TABLE IF NOT EXISTS checkout_requests (
    buyer_id BIGINT UNSIGNED NOT NULL,
    request_key CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    listing_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    PRIMARY KEY (buyer_id, request_key),
    UNIQUE KEY (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB;
