ALTER TABLE orders
    ADD COLUMN fulfilment_method ENUM('delivery','pickup') NOT NULL DEFAULT 'delivery',
    MODIFY COLUMN order_status ENUM('pending','accepted','rejected','cancelled','completed','dispatched','ready_for_pickup') NOT NULL DEFAULT 'pending';
