-- Optional fictional inventory for a freshly initialized demonstration database.
START TRANSACTION;
INSERT INTO users (role, full_name, username, email, password_hash, account_status)
VALUES ('farmer','Demo Grower','demo_grower','grower@example.invalid','disabled-demo-login','active');
SET @demo_farmer = LAST_INSERT_ID();
INSERT INTO farmer_profiles (farmer_id, farm_name, address_line, village, district, state, pincode)
VALUES (@demo_farmer,'Example Farm','Demo Road','Sample Village','Hyderabad','Telangana','500001');
INSERT INTO crop_listings (farmer_id,category_id,crop_name,variety,quantity_available,quantity_unit,price_per_unit)
SELECT @demo_farmer, category_id, 'Tomato', 'Local', 150, 'kg', 24 FROM crop_categories WHERE category_name='Vegetables';
COMMIT;
